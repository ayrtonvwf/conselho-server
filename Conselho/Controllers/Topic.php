<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Topic extends Controller
{
    public function __construct() {
        parent::__construct('topic');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'topic_type_id' => $this->input('topic_type_id') ? new ObjectId($this->input('topic_type_id')) : null,
            'updated_at' => []
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($this->input('min_updated_at')) {
            $filters['updated_at']['gte'] = new UTCDateTime($this->input('min_updated_at'));
        }
        if ($this->input('max_updated_at')) {
            $filters['updated_at']['lte'] = new UTCDateTime($this->input('max_updated_at'));
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
            'school_id' => new ObjectId($this->input('school_id')),
            'topic_type_id' => new ObjectId($this->input('topic_type_id')),
            'updated_at' => new UTCDateTime()
        ];
        
        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_TOPIC'], $this->prettify());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'name'  => ['required', ['lengthBetween', 5, 30]],
            'school_id' => ['required', 'objectId', ['inCollection', 'school']],
            'topic_type_id' => ['required', 'objectId', ['inCollection', 'topic_type']]
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
            'school_id' => $this->input('school_id') ? new ObjectId($this->input('school_id')) : null,
            'topic_type_id' => $this->input('topic_type_id') ? new ObjectId($this->input('topic_type_id')) : null,
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => new ObjectId($this->input('id'))];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE_TOPIC'], $this->prettify());
        }
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'name'  => ['optional', ['lengthBetween', 5, 30]],
            'school_id' => ['optional', 'objectId', ['inCollection', 'school']],
            'topic_type_id' => ['optional', 'objectId', ['inCollection', 'topic_type']]
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