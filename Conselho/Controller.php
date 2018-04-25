<?php
namespace Conselho;
use MongoDB, DateTime;
use Valitron\Validator;
use MongoDB\Model\BSONDocument;
use MongoDB\BSON\{ObjectId, UTCDateTime};

abstract class Controller {
    private $db;
    private $token;
    private $input_data = [];
    private $validation_errors = [];
    private $prettify = false;
    private $collection_name = null;
    
    public function __construct(string $collection_name = null) {
        $db_client = new MongoDB\Client;
        $db_name = getenv('DB_NAME');
        $this->db = $db_client->$db_name;
        $this->collection_name = $collection_name;

        $this->token = $_SERVER['HTTP_TOKEN'] ?? null;
        $this->input_data = json_decode(file_get_contents('php://input'), true) ?? [];

        $this->prettify = (bool) ($_SERVER['HTTP_PRETTIFY'] ?? false);
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
           if ($value instanceof UTCDateTime) {
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

    protected function datetime_to_string(UTCDateTime $date) : string {
        $format = 'Y-m-d';
        if (($date = $date->toDateTime()) > new DateTime(date('Y-m-d'))) {
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
        } catch (\Exception $error) {
            return null;
        }

        return $object_id;
    }

    protected function input_date(string $key) : ?UTCDateTime {
        $value = $this->input_raw($key);

        if (!$value) {
            return null;
        }

        try {
            $date = new UTCDateTime($value);
        } catch (\Exception $error) {
            return null;
        }

        return $date;
    }

    protected function get_db() : MongoDB\Database {
        return $this->db;
    }

    protected function get_collection_name() : ?string {
        return $this->collection_name;
    }

    protected function get_collection() : MongoDB\Collection {
        $collection_name = $this->get_collection_name();
        return $this->get_db()->$collection_name;
    }

    protected function get_user() : ?BSONDocument {
        if (!$this->token) {
            return null;
        }

        $db = $this->get_db();

        $token = $db->user_token->findOne(['value' => $this->token]);

        return $db->user->findOne(['_id' => $token->user_id]);
    }

    protected function get_token() : ?string {
        return $this->token;
    }

    protected function run_validation(array $rules) : bool {
        $data = array_map('strip_tags', $this->input_data);
        $data = array_map('trim', $data);
        
        $db = $this->get_db();
        $validator = new Validator($data);

        $validator->addRule('objectId', function($field, $objectId) {
            try {
                new ObjectId($objectId);
                return true; // no error on parsing
            } catch (\Exception $e) {
                return false; // cannot parse object id
            }
        }, '{field} must be a valid ObjectID');
        
        $validator->addRule('inCollection', function($field, $objectId, array $params) use ($db) {
            $collection_name = $params[0] ?? $this->get_collection_name();
            $search_criteria = ['_id' => new ObjectId($objectId)];
            return (bool) $db->$collection_name->findOne($search_criteria);
        }, '{field} not found in database');

        $validator->addRule('notInCollection', function($field, $value, array $params) use ($db) {
            $collection_name = $params[0] ?? $this->get_collection_name();
            $except_for = $params[1] ?? null;
            $search_criteria = [$field => $value];
            if (!$found = $db->$collection_name->findOne($search_criteria)) {
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