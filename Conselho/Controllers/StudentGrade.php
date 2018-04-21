<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class StudentGrade extends Controller
{
    public function __construct() {
        parent::__construct('student_grade');
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
            'grade_id' => new ObjectId($this->input('grade_id')),
            'number' => (int) $this->input('number'),
            'student_id' => new ObjectId($this->input('student_id')),
            'start' => new UTCDateTime($this->input('start')),
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
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'number' => ['required', 'integer', ['min', 1]],
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'start' => ['required', ['dateFormat', 'Y-m-d']]
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
            'grade_id' => $this->input('grade_id') ? new ObjectId($this->input('grade_id')) : null,
            'number' => (int) $this->input('number'),
            'student_id' => $this->input('student_id') ? new ObjectId($this->input('student_id')) : null,
            'start' => new UTCDateTime($this->input('start')),
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
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'number' => ['optional', 'integer', ['min', 1]],
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'start' => ['optional', ['dateFormat', 'Y-m-d']]
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