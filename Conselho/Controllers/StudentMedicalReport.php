<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class StudentMedicalReport extends Controller
{
    public function __construct() {
        parent::__construct('student_medical_report');
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
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function get_filters() : array {
        $filters = [
            '_id' => $this->input_id('id'),
            'student_id' => $this->input_id('student_id'),
            'updated_at' => []
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($subject_id = $this->input_id('subject_id')) {
            $filters['subject_ids']['$in'] = [$subject_id];
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
            'student_id' => $this->input_id('student_id'),
            'subject_ids' => array_map(function ($subject_id) { return new ObjectId($subject_id); }, $this->input('subject_ids')),
            'description' => $this->input('description'),
            'updated_at' => new UTCDateTime()
        ];

        try {
            $this->get_collection()->insertOne($data);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT_STUDENT_MEDICAL_REPORT'], $this->prettify());
        }
    }

    private function validate_post() : bool {
        $rules = [
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'subject_id' => ['required', 'array'],
            'description' => ['required', 'string', ['maxLength', 50]]
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
            'student_id' => $this->input_id('student_id'),
            'subject_ids' => array_map(function ($subject_id) { return new ObjectId($subject_id); }, $this->input('subject_ids')),
            'description' => $this->input('description'),
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => $this->input_id('id')];
        if (!$this->get_collection()->updateOne($criteria, ['$set' => $data])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE_STUDENT_MEDICAL_REPORT'], $this->prettify());
        }
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection'],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'subject_id' => ['optional', 'array'],
            'description' => ['optional', 'string', ['maxLength', 50]]
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