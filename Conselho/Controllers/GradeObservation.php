<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class GradeObservation extends Controller
{
    public function __construct() {
        parent::__construct('grade_observation');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'updated_at' => [],
            'council_id' => $this->input_id('council_id'),
            'user_id' => $this->input_id('user_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id')
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
            'council_id' => $this->input_id('council_id'),
            'user_id' => $this->input_id('user_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id'),
            'description' => $this->input('description'),
            'updated_at' => new UTCDateTime()
        ];

        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_GRADE_OBSERVATION'], $this->prettify());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'objectId', ['inCollection', 'council']],
            'user_id' => ['required', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['required', 'objectId', ['inCollection', 'subject']],
            'description' => ['required', 'string', ['maxLength', 300]]
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
            'council_id' => $this->input_id('council_id'),
            'user_id' => $this->input_id('user_id'),
            'grade_id' => $this->input_id('grade_id'),
            'subject_id' => $this->input_id('subject_id'),
            'description' => $this->input('description'),
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => $this->input_id('id')];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATED_GRADE_OBSERVATION'], $this->prettify());
        }
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'council_id' => ['optional', 'objectId', ['inCollection', 'council']],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'description' => ['optional', 'string', ['maxLength', 300]]
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