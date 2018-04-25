<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\Models;
use DateTime;

class UserToken extends Controller
{
    public function get() {
        $default_model = $this->get_default_model();
        $results = $default_model::find()->toArray();
        $results = $this->sanitize_output($results);
        $return = [
            'results' => $results
        ];
        return json_encode($return, $this->prettify());
    }

    public function post() : string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $user = Models\User::one(['email' => $this->input('email')]);
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
            $user->save();
        }

        $default_model = $this->get_default_model();
        $token = $this->generate_token();
        $token['user_id'] = $user->_id;
        $entity = new $default_model($token);
        $entity->save();

        $output = [
            'value' => $token['value'],
            'expires_at' => $token['expires_at']->format('Y-m-d H:i:s'),
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
            'expires_at' => new DateTime(new DateTime('+1 day'))
        ];
    }

    public function delete() : void {
        $current_token = $this->get_token();
        UserToken::one(['value' => $current_token])->delete();
    }
}