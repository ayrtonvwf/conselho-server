<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\UTCDateTime;

class Role extends Controller
{
    public function __construct() {
        parent::__construct('role');
    }

    public function get() {
        if (!$this->validate_get()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $pagination = $this->get_pagination();
        $results = $collection->find($filters, $pagination)->toArray();
        $results = $this->sanitize_output($results);
        $return = [
            'results' => $results,
            'all_results' => $collection->count($filters),
            'per_page' => $pagination['limit']
        ];
        return json_encode($return, $this->prettify());
    }

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'objectId', 'inCollection'],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'role_type_id' => ['optional', 'objectId', ['inCollection', 'role_type']],
            'school_id' => ['optional', 'objectId', ['inCollection', 'school']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'aproved'  => ['optional', 'boolean'],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function get_filters() : array {
        $filters = [
            '_id' => $this->input_id('id'),
            'user_id' => $this->input_id('user_id'),
            'role_type_id' => $this->input_id('role_type_id'),
            'school_id' => $this->input_id('school_id'),
            'updated_at' => [],
        ];
        if ($min_updated_at = $this->input_date('min_updated_at')) {
            $filters['updated_at']['gte'] = $min_updated_at;
        }
        if ($max_updated_at = $this->input_date('max_updated_at')) {
            $filters['updated_at']['lte'] = $max_updated_at;
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
            'user_id' => $this->input_id('user_id'),
            'role_type_id' => $this->input_id('role_type_id'),
            'school_id' => $this->input_id('school_id'),
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
            'user_id' => $this->input_id('user_id'),
            'role_type_id' => $this->input_id('role_type_id'),
            'school_id' => $this->input_id('school_id'),
            'aproved' => (bool) $this->input('aproved'),
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => $this->input_id('id')];
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
        
        $this->get_collection()->deleteOne(['_id' => $this->input_id('id')]);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection']
        ];

        return $this->run_validation($rules);
    }
}