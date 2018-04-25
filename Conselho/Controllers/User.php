<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\UTCDateTime;

class User extends Controller
{
    public function __construct() {
        parent::__construct('user');
    }

    public function get() {
        $collection = $this->get_collection();
        $filter = $this->get_filters();
        $results = $collection->find($filter)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'updated_at' => []
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($min_updated_at = $this->input_date('min_updated_at')) {
            $filters['updated_at']['gte'] = $min_updated_at;
        }
        if ($max_updated_at = $this->input_date('max_updated_at')) {
            $filters['updated_at']['lte'] = $max_updated_at;
        }
        return array_filter($filters);
    }

    public function post() {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => password_hash($this->input_raw('password'), PASSWORD_DEFAULT),
            'dev' => false,
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_USER'], $this->prettify());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'name'  => ['required', ['lengthBetween', 5, 100]],
            'email' => ['required', 'email', ['lengthBetween', 5, 200], 'notInCollection'],
            'password' => ['required', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    public function put() {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $user = $this->get_user();

        $data = array_filter([
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => $this->input('password') ? password_hash($this->input('password'), PASSWORD_DEFAULT) : null,
            'updated_at' => new UTCDateTime()
        ]);

        if (!$this->get_collection()->updateOne(['_id' => $user->_id], ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE_USER'], $this->prettify());
        }
    }

    private function validate_put() : bool {
        $rules = [
            'name'  => ['optional', ['lengthBetween', 5, 100]],
            'email' => ['optional', 'email', ['lengthBetween', 5, 200]],
            'password' => ['optional', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    public function delete() {
        $user = $this->get_user();
        $this->get_collection()->deleteOne(['_id' => $user->_id]);
    }
}