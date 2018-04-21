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
            'student_id' => new ObjectId($this->input('student_id')),
            'subject_ids' => array_map(function ($subject_id) { return new ObjectId($this->input('subject_id')); }, $this->input('subject_ids')),
            'description' => $this->input('description'),
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
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'subject_id' => ['required', 'array'],
            'description' => ['required', 'string', ['maxLength', 50]]
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
            'student_id' => new ObjectId($this->input('student_id')),
            'subject_ids' => array_map(function ($subject_id) { return new ObjectId($this->input('subject_id')); }, $this->input('subject_ids')),
            'description' => $this->input('description'),
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
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'subject_id' => ['optional', 'array'],
            'description' => ['optional', 'string', ['maxLength', 50]]
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