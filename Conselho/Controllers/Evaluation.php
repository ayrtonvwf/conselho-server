<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Evaluation extends Controller
{
    public function __construct() {
        parent::__construct('evaluation');
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
            'student_id' => new ObjectId($this->input('student_id')),
            'grade_id' => new ObjectId($this->input('grade_id')),
            'subject_id' => new ObjectId($this->input('subject_id')),
            'council_id' => new ObjectId($this->input('council_id')),
            'topic_id' => new ObjectId($this->input('topic_id')),
            'value' => $this->input('value'),
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
            'student_id' => ['required', 'objectId', ['inCollection', 'student']],
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['required', 'objectId', ['inCollection', 'subject']],
            'council_id' => ['required', 'objectId', ['inCollection', 'council']],
            'topic_id' => ['required', 'objectId', ['inCollection', 'topic']],
            'value' => ['required']
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
            'student_id' => $this->input('student_id') ? new ObjectId($this->input('student_id')) : null,
            'grade_id' => $this->input('grade_id') ? new ObjectId($this->input('grade_id')) : null,
            'subject_id' => $this->input('subject_id') ? new ObjectId($this->input('subject_id')) : null,
            'council_id' => $this->input('council_id') ? new ObjectId($this->input('council_id')) : null,
            'topic_id' => $this->input('topic_id') ? new ObjectId($this->input('topic_id')) : null,
            'value' => $this->input('value'),
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
            'student_id' => ['optional', 'objectId', ['inCollection', 'student']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'council_id' => ['optional', 'objectId', ['inCollection', 'council']],
            'topic_id' => ['optional', 'objectId', ['inCollection', 'topic']],
            'value' => ['optional']
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