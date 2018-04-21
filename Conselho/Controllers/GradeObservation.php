<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class GradeObservation extends Controller
{
    public function __construct() {
        parent::__construct('grade_observation');
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
            'council_id' => new ObjectId($this->input('council_id')),
            'user_id' => new ObjectId($this->input('user_id')),
            'grade_id' => new ObjectId($this->input('grade_id')),
            'subject_id' => new ObjectId($this->input('subject_id')),
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
            'council_id' => ['required', 'objectId', ['inCollection', 'council']],
            'user_id' => ['required', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['required', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['required', 'objectId', ['inCollection', 'subject']],
            'description' => ['required', 'string', ['maxLength', 300]]
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
            'council_id' => $this->input('council_id') ? new ObjectId($this->input('council_id')) : null,
            'user_id' => $this->input('user_id') ? new ObjectId($this->input('user_id')) : null,
            'grade_id' => $this->input('grade_id') ? new ObjectId($this->input('grade_id')) : null,
            'subject_id' => $this->input('subject_id') ? new ObjectId($this->input('subject_id')) : null,
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
            'council_id' => ['optional', 'objectId', ['inCollection', 'council']],
            'user_id' => ['optional', 'objectId', ['inCollection', 'user']],
            'grade_id' => ['optional', 'objectId', ['inCollection', 'grade']],
            'subject_id' => ['optional', 'objectId', ['inCollection', 'subject']],
            'description' => ['optional', 'string', ['maxLength', 300]]
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