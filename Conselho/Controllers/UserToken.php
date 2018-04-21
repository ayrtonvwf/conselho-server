<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;
use MongoDB\BSON\UTCDateTime;
use DateTime, DateInterval;

class UserToken extends Controller
{
    public function __construct() {
        parent::__construct('user_token');
    }

    public function get(Request $request) {
        $collection = $this->get_collection();
        $results = $collection->find([])->toArray();
        return json_encode($results, $this->prettify());
    }

    public function post(Request $request) : string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'errors' => $this->get_validation_errors()
            ], $this->prettify());
        }
        
        $db = $this->get_db();

        $user = $db->user->findOne(['email' => $this->input('email')]);
        if (!$user) {
            http_response_code(400);
            return json_encode(['error' => 'EMAIL_NOT_FOUND'], $this->prettify());
        }
        
        if (!password_verify($this->input_raw('password'), $user->password)) {
            http_response_code(400);
            return json_encode(['error' => 'WRONG_PASSWORD'], $this->prettify());
        }

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $user->password = password_hash($this->input('password'), PASSWORD_DEFAULT);
            $db->user->updateOne(['_id' => $user->_id], ['$set' => ['password' => $user->password]]);
        }

        $token = $this->generate_token();
        $token['user_id'] = $user->_id;
        $token['updated_at'] = new UTCDateTime();
        
        $this->get_collection()->insertOne($token);

        $output = [
            'value' => $token['value'],
            'expires_at' => $token['expires_at']->toDateTime()->format('Y-m-d H:i:s'),
            'user_id' => (string) $token['user_id']
        ];
        return json_encode($output, $this->prettify());
    }

    private function validate_post() : bool {
        $rules = [
            'email' => ['required', 'email', ['lengthBetween', 5, 200]],
            'password' => ['required', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    private function generate_token() : array {
        return [
            'value' => sodium_bin2hex(random_bytes(32)),
            'expires_at' => new UTCDateTime(new DateTime('+1 day'))
        ];
    }

    public function delete(Request $request) : void {
        $current_token = $this->get_token();

        $this->get_collection()->deleteOne(['value' => $current_token]);
    }
}