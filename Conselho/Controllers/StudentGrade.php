<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\UTCDateTime;

class StudentGrade extends Controller
{
    public function __construct() {
        parent::__construct('student_grade');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            '_id' => $this->input_id('id'),
            'grade_id' => $this->input_id('grade_id'),
            'student_id' => $this->input_id('student_id'),
            'updated_at' => []
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($this->input('min_number')) {
            $filters['number']['gte'] = $this->input('min_number');
        }
        if ($this->input('max_number')) {
            $filters['number']['lte'] = $this->input('max_number');
        }
        if ($this->input('min_start')) {
            $filters['start']['gte'] = $this->input('min_start');
        }
        if ($this->input('max_start')) {
            $filters['start']['lte'] = $this->input('max_start');
        }
        if ($min_updated_at = $this->input('min_updated_at')) {
            $filters['updated_at']['gte'] = $min_updated_at;
        }
        if ($max_updated_at = $this->input('max_updated_at')) {
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
            'grade_id' => $this->input_id('grade_id'),
            'number' => (int) $this->input('number'),
            'student_id' => $this->input_id('student_id'),
            'start' => $this->input_date('start'),
            'updated_at' => new UTCDateTime()
        ];

        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_STUDENT_GRADE'], $this->prettify());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'number' => ['required', 'integer', ['min', 1]],
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'start' => ['required', ['dateFormat', 'Y-m-d']]
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
            'grade_id' => $this->input_id('grade_id'),
            'number' => (int) $this->input('number'),
            'student_id' => $this->input_id('student_id'),
            'start' => $this->input_date('start'),
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => $this->input_id('id')];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE_STUDENT_GRADE'], $this->prettify());
        }
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'number' => ['optional', 'integer', ['min', 1]],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'start' => ['optional', ['dateFormat', 'Y-m-d']]
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