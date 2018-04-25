<?php
namespace Conselho;
use DateTime, Exception;
use Valitron\Validator;
use MongoDB\BSON\ObjectId;
use Conselho\Models;

abstract class Controller {
    private $token;
    private $input_data = [];
    private $validation_errors = [];
    private $prettify = false;
    private $default_model;
    
    public function __construct()
    {
        $this->setup_db();
        $this->default_model = '\\Conselho\\Models\\'.get_called_class();

        $this->token = $_SERVER['HTTP_TOKEN'] ?? null;
        $this->input_data = json_decode(file_get_contents('php://input'), true) ?? [];

        $this->prettify = (bool)($_SERVER['HTTP_PRETTIFY'] ?? false);
    }

    protected function get_default_model() : string {
        return $this->default_model;
    }

    private function setup_db() : void {
        $db_name = getenv('DB_NAME');
        \Purekid\Mongodm\MongoDB::setConfigBlock($db_name, [
            'connection' => [
                'hostnames' => 'localhost',
                'database' => $db_name,
                'options' => []
            ]
        ]);
    }

    protected function get_pagination(int $limit = 50) : array {
        $page = (int) $this->input('page');
        $skip = $limit*$page-1;
        return [
            'limit' => $limit,
            'skip' => $skip
        ];
    }

    protected function sanitize_output($output) : array {
        $output = (array) $output;

        $output = array_map(function($value) {
           if (is_array($value)) {
               return $this->sanitize_output($value);
           }
           if ($value instanceof ObjectId) {
               return (string) $value;
           }
           if ($value instanceof DateTime) {
               return $this->datetime_to_string($value);
           }
           return $value;
        }, $output);

        if (isset($output['_id'])) {
            $output['id'] = $output['_id'];
            $id = new ObjectId($output['id']);
            $output['created_at'] = date('Y-m-d H:i:s', $id->getTimestamp());
            unset($output['id']);
        }

        return $output;
    }

    protected function datetime_to_string(DateTime $date) : string {
        $format = 'Y-m-d';
        if ($date > new DateTime(date('Y-m-d'))) {
            $format .= ' H:i:s';
        }
        return $date->format($format);
    }

    protected function prettify() : ?int {
        return $this->prettify ? JSON_PRETTY_PRINT : null;
    }
    
    protected function input_raw(string $key) : ?string {
        return $this->input_data[$key] ?? null;
    }

    protected function input(string $key) : ?string {
        $value = $this->input_raw($key);

        return !is_null($value) ? trim(strip_tags($value)) : null;
    }

    protected function input_id(string $key) : ?ObjectId {
        $value = $this->input_raw($key);

        if (!$value) {
            return null;
        }

        try {
            $object_id = new ObjectId($value);
        } catch (Exception $error) {
            return null;
        }

        return $object_id;
    }

    protected function input_date(string $key) : ?DateTime {
        $value = $this->input_raw($key);

        if (!$value) {
            return null;
        }

        try {
            $date = new DateTime($value);
        } catch (Exception $error) {
            return null;
        }

        return $date;
    }

    protected function get_user() : Models\User {
        $user_token = Models\UserToken::find(['value' => $this->token]);
        return Models\User::one(['_id' => $user_token->user_id]);
    }

    protected function get_token() : ?string {
        return $this->token;
    }

    protected function run_validation(array $rules) : bool {
        $data = array_map('strip_tags', $this->input_data);
        $data = array_map('trim', $data);

        $default_model = $this->default_model;

        $validator = new Validator($data);

        $validator->addRule('objectId', function($field, $objectId) {
            try {
                new ObjectId($objectId);
                return true; // no error on parsing
            } catch (\Exception $e) {
                return false; // cannot parse object id
            }
        }, '{field} must be a valid ObjectID');

        $validator->addRule('inCollection', function($field, $objectId, array $params) use ($default_model) {
            $model = "\\Council\\Models\\$params[0]" ?? $default_model;
            $search_criteria = ['_id' => new ObjectId($objectId)];
            return (bool) $model::one($search_criteria);
        }, '{field} not found in database');

        $validator->addRule('notInCollection', function($field, $value, array $params) use ($default_model) {
            $model = "\\Council\\Model\\$params[0]" ?? $default_model;
            $except_for = $params[1] ?? null;
            $search_criteria = [$field => $value];
            if (!$found = $model::one($search_criteria)) {
                return true;
            }
            return $except_for && $found->_id == $except_for;
        }, '{field} already exists in database');

        $validator->mapFieldsRules($rules);
        $validator->validate();
        $this->validation_errors = $validator->errors();

        return !$this->validation_errors;
    }

    protected function get_validation_errors() : array {
        return $this->validation_errors;
    }
}