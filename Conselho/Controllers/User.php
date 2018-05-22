<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\User\UserMapper;

class User extends Controller
{

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
        $rules = [
            'id' => ['optional', 'integer'],
            'active'  => ['optional', 'integer', ['in', [0, 1]]],
            'min_created_at' => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'max_created_at' => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'min_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'max_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $atlas = $this->atlas();
        $email_exists = function($field, $email) use ($atlas) : bool {
            $user = $atlas->fetchRecordBy(UserMapper::CLASS, ['email' => $email]);
            return !$user;
        };
        $rules = [
            'name'  => ['required', ['lengthBetween', 5, 100]],
            'email' => ['required', 'email', ['lengthBetween', 5, 200], [$email_exists, 'message' => 'This email is already registered']],
            'password' => ['required', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch($user) : bool {
        $atlas = $this->atlas();
        $email_exists = function($field, $email) use ($atlas, $user) : bool {
            if ($email == $user->email) {
                return true;
            }
            $user = $atlas->fetchRecordBy(UserMapper::CLASS, ['email' => $email]);
            return !$user;
        };
        $rules = [
            'name'  => ['optional', ['lengthBetween', 5, 100]],
            'email' => ['optional', 'email', ['lengthBetween', 5, 200], [$email_exists, 'message' => 'This email is already registered']],
            'password' => ['optional', ['lengthBetween', 5, 32]]
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $atlas = $this->atlas();
        $select = $atlas->select(UserMapper::CLASS);
        if ($id = $this->input_int('id')) {
            $select->where('id = ?', $id);
        }
        if ($min_created_at = $this->input_datetime('min_created_at')) {
            $select->where('created_at >= ?', $min_created_at);
        }
        if ($max_created_at = $this->input_datetime('max_created_at')) {
            $select->where('created_at <= ?', $max_created_at);
        }
        if ($min_updated_at = $this->input_datetime('min_updated_at')) {
            $select->where('updated_at >= ?', $min_updated_at);
        }
        if ($max_updated_at = $this->input_datetime('max_updated_at')) {
            $select->where('updated_at <= ?', $max_updated_at);
        }
        if ($search = $this->input_string('search')) {
            $select->where('(name LIKE ?', "%$search%");
            $select->orWhere('email LIKE ?)', "%$search%");
        }
        if (!is_null($active = $this->input_bool('active'))) {
            $select->where('active = ?', $active);
        }
        $pagination = $this->get_pagination();
        $select->limit($pagination['limit']);
        $select->offset($pagination['offset']);
        $select->cols(['id', 'name', 'email', 'active', 'created_at', 'updated_at']);

        $results = array_map(function($result) {
            $result['created_at'] = $this->output_datetime($result['created_at']);
            $result['updated_at'] = $this->output_datetime($result['updated_at']);
            return $result;
        }, $select->fetchAll());

        $return = [
            'total_results' => $select->fetchCount(),
            'current_page' => $pagination['page'],
            'max_results_per_page' => $pagination['limit'],
            'results' => $results
        ];
        return json_encode($return, $this->pretty());
    }

    public function post() : ?string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $data = $this->get_post_data();
        $atlas = $this->atlas();
        $user = $atlas->newRecord(UserMapper::CLASS, $data);
        if (!$atlas->insert($user)) {
            http_response_code(500);
            return null;
        }
        $output = [
            'id' => $user->id,
            'created_at' => $this->output_datetime($user->created_at)
        ];
        return json_encode($output, $this->pretty());
    }

    public function patch() : ?string {
        $user = $this->get_user();

        if (!$this->validate_patch($user)) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $data = $this->get_patch_data();
        $user->set($data);
        $atlas = $this->atlas();
        if (!$atlas->update($user)) {
            http_response_code(500);
            return null;
        }
        return json_encode(['updated_at' => $this->output_datetime($user->updated_at)], $this->pretty());
    }

    public function delete() : void {
        $user = $this->get_user();
        $atlas = $this->atlas();
        $blocking_dependencies = ['evaluations', 'grade_observations', 'student_observations', 'teachers'];

        $user = $atlas->fetchRecord(UserMapper::CLASS, $user->id, $blocking_dependencies);
        $has_blocking_dependency = array_filter($blocking_dependencies, function($dependency) use ($user) {
            return (bool) $user->$dependency;
        });
        if ($has_blocking_dependency) {
            http_response_code(409);
            return;
        }

        $full_dependencies = array_merge($blocking_dependencies, ['roles', 'user_tokens', 'teacher_requests']);
        $user = $atlas->fetchRecord(UserMapper::CLASS, $user->id, $full_dependencies);
        $transaction = $atlas->newTransaction();
        foreach ($full_dependencies as $dependency_name) {
            foreach ($user->$dependency_name as $dependency) {
                $transaction->delete($dependency);
            }
        }
        $transaction->delete($user);
        if (!$transaction->exec()) {
            http_response_code(500);
            echo json_encode($transaction->getException(), $this->pretty());
            return;
        }

        http_response_code(204);
        return;
    }
}