<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class StudentMedicalReport extends Controller
{
    public function __construct() {
        parent::__construct('student_medical_report');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'student_id' => $this->input('student_id') ? new ObjectId($this->input('student_id')) : null,
            'updated_at' => []
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($this->input('subject_id')) {
            $filters['subject_ids']['$in'] = [new ObjectId($this->input('subject_id'))];
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
            'student_id' => new ObjectId($this->input('student_id')),
            'subject_ids' => array_map(function ($subject_id) { return new ObjectId($this->input('subject_id')); }, $this->input('subject_ids')),
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
            'student_id' => new ObjectId($this->input('student_id')),
            'subject_ids' => array_map(function ($subject_id) { return new ObjectId($this->input('subject_id')); }, $this->input('subject_ids')),
            'description' => $this->input('description'),
            'updated_at' => new UTCDateTime()
        ]);

        $criteria = ['_id' => new ObjectId($this->input('id'))];
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
        
        $this->get_collection()->deleteOne(['_id' => $this->input('id')]);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection']
        ];

        return $this->run_validation($rules);
    }
}