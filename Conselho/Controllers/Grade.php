<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Grade extends Controller
{
    public function __construct() {
        parent::__construct('grade');
    }

    public function get() {
        $collection = $this->get_collection();
        $results = $collection->find([])->toArray();
        return json_encode(['results' => $results], $this->prettify());
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
            'level' => (int) $this->input('level'),
            'school_id' => new ObjectId($this->input('school_id')),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_GRADE'], $this->prettify());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'name'  => ['required', ['lengthBetween', 1, 50]],
            'level'  => ['required', 'integer', ['min', 1]],
            'school_id' => ['required', 'objectId', ['inCollection', 'school']]
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

        $data = array_filter([
            'name' => $this->input('name'),
            'level' => (int) $this->input('level'),
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => new ObjectId($this->input('id'))];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE_GRADE'], $this->prettify());
        }
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'name'  => ['optional', ['lengthBetween', 1, 50]],
            'level'  => ['required', 'integer', ['min', 1]],
            'school_id' => ['optional', 'objectId', ['inCollection', 'school']]
        ];

        return $this->run_validation($rules);
    }

    public function delete() {
        if (!$this->validate_delete()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }
        
        $this->get_collection()->deleteOne(['_id' => $this->input('id')]);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection']
        ];

        return $this->run_validation($rules);
    }
}