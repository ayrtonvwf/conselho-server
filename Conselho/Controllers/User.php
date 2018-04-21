<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class User extends Controller
{
    public function __construct() {
        parent::__construct('user');
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
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => password_hash($this->input_raw('password'), PASSWORD_DEFAULT),
            'dev' => false,
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
            'name'  => ['required', ['lengthBetween', 5, 100]],
            'email' => ['required', 'email', ['lengthBetween', 5, 200], 'notInCollection'],
            'password' => ['required', ['lengthBetween', 5, 32]]
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

        $user = $this->get_user();

        $data = array_filter([
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => $this->input('password') ? password_hash($this->input('password'), PASSWORD_DEFAULT) : null,
            'updated_at' => new UTCDateTime()
        ]);

        if (!$this->get_collection()->updateOne(['_id' => $user->_id], ['$set' => $data])) {
            http_response_code(500);
            return;
        }
    }

    private function validate_put() : bool {
        $rules = [
            'name'  => ['optional', ['lengthBetween', 5, 100]],
            'email' => ['optional', 'email', ['lengthBetween', 5, 200]],
            'password' => ['optional', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    public function delete(Request $request) {
        $user = $this->get_user();
        $this->get_collection()->deleteOne(['_id' => $user->_id]);
    }
}