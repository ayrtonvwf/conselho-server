<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Permission\PermissionMapper;
use Conselho\DataSource\User\UserMapper;
use PDO;

class User extends Controller
{
    private function get_filters() : array {
        return array_filter([
            'id' => $this->input_int('id'),
            'search' => $this->input_string('search'),
            'max_updated_at' => $this->input_string('max_updated_at'),
            'min_updated_at' => $this->input_string('min_updated_at')
        ]);
    }

    private function get_patch_data() : array {
        return array_filter([
            'name' => $this->input_string('name'),
            'email' => $this->input_string('email'),
            'password' => $this->input_raw('password') ? password_hash($this->input_raw('password'), PASSWORD_DEFAULT) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function get_post_data() : array {
        $now = date('Y-m-d H:i:s');
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
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d H:i:s']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d H:i:s']],
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
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $filters = $this->get_filters();

        $where = [];
        if (isset($filters['id'])) {
            $where[] = '`id` = :id';
        }
        if (isset($filters['max_updated_at'])) {
            $where[] = '`updated_at` <= :max_updated_at';
        }
        if (isset($filters['min_updated_at'])) {
            $where[] = '`updated_at` >= :min_updated_at';
        }
        if (isset($filters['search'])) {
            $where[] = '(`name` LIKE %:search% OR `email` LIKE %:search%)';
        }

        $where = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $pagination = $this->get_pagination();

        $sql = "SELECT * FROM `user` $where LIMIT :limit OFFSET :offset";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);

        $parameters = $filters + $pagination;
        foreach ($parameters as $parameter_name => $parameter_value) {
            $statement->bindValue(":$parameter_name", $parameter_value, is_int($parameter_value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        if (!$statement->execute()) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_QUERY'], $this->pretty());
        }

        $results = $statement->fetchAll(PDO::FETCH_OBJ);
        // filter output columns

        $sql = "SELECT COUNT(*) AS all_results FROM user $where";
        $statement = $db->prepare($sql);
        $statement->execute($filters);
        $all_results = (int) $statement->fetchObject()->all_results;

        $return = [
            'results' => $results,
            'all_results' => $all_results,
            'per_page' => $pagination['limit']
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

    public function delete() : string {
        $sql = "DELETE FROM `user` WHERE `id` = :id";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute(['id' => $this->get_user()->id])) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_DELETE'], $this->pretty());
        }
        return json_encode(['error_code' => null], $this->pretty());
    }
}