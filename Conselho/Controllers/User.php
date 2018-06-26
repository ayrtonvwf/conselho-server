<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\User\UserMapper;

class User extends Controller
{
    public function __construct()
    {
        parent::__construct(UserMapper::class);
    }

    private function get_get_data() : array {
        $search_values = array_filter([
            'name_search' => $search = $this->input_search('search'),
            'email_search' => $search
        ]);
        $where = array_filter([
            '(name LIKE :name_search OR email LIKE :email_search)' => $search_values
        ]);
        if (!is_null($active = $this->input_bool('active'))) {
            $where['active = ?'] = $active;
        }
        return $where;
    }

    private function get_patch_data() : array {
        $data = array_filter([
            'name' => $this->input_string('name'),
            'email' => $this->input_string('email'),
            'password' => $this->input_raw('password') ? password_hash($this->input_raw('password'), PASSWORD_DEFAULT) : null,
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ]);
        if (!is_null($active = $this->input_bool('active'))) {
            $data['active'] = $active;
        }
        return $data;
    }

    private function get_post_data() : array {
        $now = date(self::DATETIME_INTERNAL_FORMAT);
        return [
            'name' => $this->input_string('name'),
            'email' => $this->input_string('email'),
            'password' => password_hash($this->input_raw('password'), PASSWORD_DEFAULT),
            'created_at' => $now,
            'updated_at' => $now
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'active'  => ['optional', 'integer', ['in', [0, 1]]],
            'search'  => ['optional', ['lengthBetween', 3, 50]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $atlas = $this->atlas();
        $email_exists = function($field, $email) use ($atlas) : bool {
            $user = $atlas->fetchRecordBy($this->mapper_class_name, ['email' => $email]);
            return !$user;
        };
        $rules = [
            'name'  => ['required', ['lengthBetween', 3, 50]],
            'email' => ['required', 'email', ['lengthBetween', 3, 100], [$email_exists, 'message' => 'This email is already registered']],
            'password' => ['required', ['lengthBetween', 5, 50]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch($user) : bool {
        $atlas = $this->atlas();
        $email_exists = function($field, $email) use ($atlas, $user) : bool {
            if ($email == $user->email) {
                return true;
            }
            $user = $atlas->fetchRecordBy($this->mapper_class_name, ['email' => $email]);
            return !$user;
        };
        $rules = [
            'name'  => ['optional', ['lengthBetween', 5, 100]],
            'email' => ['optional', 'email', ['lengthBetween', 5, 200], [$email_exists, 'message' => 'This email is already registered']],
            'password' => ['optional', ['lengthBetween', 5, 50]]
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return $this->input_error_output();
        }

        $where = $this->get_get_data();
        $cols = ['id', 'name', 'email', 'active', 'created_at', 'updated_at'];

        $result = $this->search($where, $cols);

        return json_encode($result, $this->pretty());
    }

    public function post() : ?string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $data = $this->get_post_data();
        if (!$record = $this->insert($data)) {
            http_response_code(500);
            return null;
        }

        return $this->post_output($record);
    }

    public function patch() : ?string {
        $record = $this->get_user();

        if (!$this->validate_patch($record)) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $data = $this->get_patch_data();
        $record->set($data);
        if (!$this->atlas()->update($record)) {
            http_response_code(500);
            return null;
        }

        return $this->patch_output($record);
    }

    public function delete() : void {
        $record = $this->get_user();

        $blocking_dependencies = ['evaluations', 'grade_observations', 'student_observations', 'teachers'];

        if (!$this->delete_with_dependencies($record, $blocking_dependencies)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}