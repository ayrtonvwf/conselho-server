<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Role extends Controller
{
    public function __construct() {
        parent::__construct('role');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'user_id' => $this->input('user_id') ? new ObjectId($this->input('user_id')) : null,
            'role_type_id' => $this->input('role_type_id') ? new ObjectId($this->input('role_type_id')) : null,
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'updated_at' => [],
        ];
        if ($this->input('min_updated_at')) {
            $filters['updated_at']['gte'] = new UTCDateTime($this->input('min_updated_at'));
        }
        if ($this->input('max_updated_at')) {
            $filters['updated_at']['lte'] = new UTCDateTime($this->input('max_updated_at'));
        }
        $filters = array_filter($filters);
        if ($this->input('aproved') !== null) {
            $filters['aproved'] = (bool) $this->input('aproved');
        }
        return $filters;
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
            'user_id' => new ObjectId($this->input('user_id')),
            'role_type_id' => new ObjectId($this->input('role_type_id')),
            'school_id' => new ObjectId($this->input('school_id')),
            'aproved' => (bool) $this->input('aproved'),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_ROLE'], $this->prettify());
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

    public function put() {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                get_validation_errors()
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
            return json_encode(['error' => 'CANNOT_UPDATE_ROLE'], $this->prettify());
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