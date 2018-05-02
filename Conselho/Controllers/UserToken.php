<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class UserToken extends Controller
{
    private function generate_token(int $user_id) : array {
        return [
            'user_id' => $user_id,
            'value' => sodium_bin2hex(random_bytes(32)),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ];
    }

    // VALIDATION

    private function validate_post() : bool {
        $rules = [
            'email' => ['required', 'email', ['lengthBetween', 5, 200]],
            'password' => ['required', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function post() : string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $db = $this->get_db_connection();
        $sql = 'SELECT * FROM `user` WHERE `email` = :email';
        $statement = $db->prepare($sql);
        $email = $this->input_string('email');
        $statement->bindValue('email', $email, PDO::PARAM_STR);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_OBJ);
        if (!$user) {
            http_response_code(400);
            return json_encode(['error_code' => 'EMAIL_NOT_FOUND'], $this->prettify());
        }
        
        if (!password_verify($this->input_raw('password'), $user->password)) {
            http_response_code(400);
            return json_encode(['error_code' => 'WRONG_PASSWORD'], $this->prettify());
        }

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $user->password = password_hash($this->input_raw('password'), PASSWORD_DEFAULT);
            $user->save();
        }

        $token = $this->generate_token($user->id);
        $sql = "INSERT INTO user_token (value, expires_at, user_id) VALUES (:value, :expires_at, :user_id)";
        $statement = $db->prepare($sql);
        if (!$statement->execute($token)) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_SAVE_TOKEN'], $this->prettify());
        }

        return json_encode($token, $this->prettify());
    }
}