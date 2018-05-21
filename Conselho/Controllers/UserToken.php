<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\User\UserMapper;
use Conselho\DataSource\UserToken\UserTokenMapper;
use PDO, Exception;

class UserToken extends Controller
{
    private function generate_token(int $user_id) : ?array {
        try {
            $token_value = sodium_bin2hex(random_bytes(32));
        } catch (Exception $exception) {
            return null;
        }
        return [
            'user_id' => $user_id,
            'value' => $token_value,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ];
    }

    // VALIDATION

    private function validate_post() : bool {
        $atlas = $this->atlas();
        $exists_email = function($field, $email) use ($atlas) {
            $user = $atlas->fetchRecordBy(UserMapper::CLASS, ['email' => $email]);
            return (bool) $user;
        };
        $rules = [
            'email' => ['required', 'email', ['lengthBetween', 5, 200], [$exists_email, 'This email is not registered']],
            'password' => ['required', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function post() : ?string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $atlas = $this->atlas();
        $user = $atlas->fetchRecordBy(UserMapper::CLASS, ['email' => $this->input_string('email')]);
        
        if (!password_verify($this->input_raw('password'), $user->password)) {
            http_response_code(401);
            return null;
        }

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $user->password = password_hash($this->input_raw('password'), PASSWORD_DEFAULT);
            $atlas->update($user);
        }

        $token_data = $this->generate_token($user->id);
        if (!$token_data) {
            http_response_code(500);
            return null;
        }
        $user_token = $atlas->newRecord(UserTokenMapper::CLASS, $token_data);
        $now = date('Y-m-d H:i:s');
        $user_token->set([
            'created_at' => $now,
            'updated_at' => $now
        ]);
        if (!$atlas->insert($user_token)) {
            http_response_code(500);
            return null;
        }

        $output = $token_data;
        $output['expires_at'] = $this->output_datetime($output['expires_at']);
        return json_encode($output, $this->pretty());
    }
}