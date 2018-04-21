<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Role extends Controller
{
    public function __construct() {
        parent::__construct('role');
    }

    public function get(Request $request) {
        $collection = $this->get_collection();
        $results = $collection->find([])->toArray();
        return json_encode($results, $this->prettify());
    }
    
    public function post(Request $request) {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'errors' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = [
            'user_id' => new ObjectId($this->input('user_id')),
            'role_type_id' => new ObjectId($this->input('role_type_id')),
            'school_id' => new ObjectId($this->input('school_id')),
            'aproved' => (bool) $this->input('aproved'),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            print_r($e->getWriteResult());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'user_id' => ['required', 'objectId', ['inCollection', 'user']],
            'role_type_id' => ['required', 'objectId', ['inCollection', 'role_type']],
            'school_id' => ['required', 'objectId', ['inCollection', 'school']],
            'aproved' => ['required', 'boolean']
        ];

        return $this->run_validation($rules);
    }

    public function put(Request $request) {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'errors' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = array_filter([
            'user_id' => $this->input('user_id') ? new ObjectId($this->input('user_id')) : null,
            'role_type_id' => $this->input('role_type_id') ? new ObjectId($this->input('role_type_id')) : null,
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'aproved' => (bool) $this->input('aproved'),
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => new ObjectId($this->input('id'))];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return;
        }
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'role_type_id' => ['optional', 'objectId', ['inCollection', 'role_type']],
            'school_id' => ['optional', 'objectId', ['inCollection', 'school']],
            'aproved' => ['optional', 'boolean']
        ];

        return $this->run_validation($rules);
    }

    public function delete(Request $request) {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'errors' => $this->get_validation_errors()
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