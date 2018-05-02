<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class Role extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'user_id' => $this->input_int('user_id'),
            'role_type_id' => $this->input_int('role_type_id'),
            'school_id' => $this->input_int('school_id'),
            'min_updated_at' => $this->input_string('min_updated_at'),
            'max_updated_at' => $this->input_string('max_updated_at')
        ];
        $filters = array_filter($filters);
        if ($this->input_bool('approved') !== null) {
            $filters['approved'] = $this->input_bool('approved');
        }
        return $filters;
    }

    private function get_data() : array {
        return [
            'user_id' => $this->input_int('user_id'),
            'role_type_id' => $this->input_int('role_type_id'),
            'school_id' => $this->input_int('school_id'),
            'approved' => $this->input_bool('approved')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'objectId'],
            'user_id' => ['optional', 'objectId'],
            'role_type_id' => ['optional', 'objectId'],
            'school_id' => ['optional', 'objectId'],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'approved'  => ['optional', 'boolean'],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'user_id' => ['required', 'objectId'],
            'role_type_id' => ['required', 'objectId'],
            'school_id' => ['required', 'objectId'],
            'approved' => ['required', 'boolean']
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'objectId'],
            'user_id' => ['optional', 'objectId'],
            'role_type_id' => ['optional', 'objectId'],
            'school_id' => ['optional', 'objectId'],
            'approved' => ['optional', 'boolean']
        ];

        return $this->run_validation($rules);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId']
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() {
        if (!$this->validate_get()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $filters = $this->get_filters();

        $where = [];
        if (isset($filters['id'])) {
            $where[] = '`id` = :id';
        }
        if (isset($filters['user_id'])) {
            $where[] = '`user_id` = :user_id';
        }
        if (isset($filters['role_type_id'])) {
            $where[] = '`role_type_id` = :role_type_id';
        }
        if (isset($filters['school_id'])) {
            $where[] = '`school_id` = :school_id';
        }
        if (isset($filters['approved'])) {
            $where[] = '`approved` = :approved';
        }
        if (isset($filters['max_updated_at'])) {
            $where[] = '`updated_at` <= :max_updated_at';
        }
        if (isset($filters['min_updated_at'])) {
            $where[] = '`updated_at` >= :min_updated_at';
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
            return json_encode(['error' => 'CANNOT_QUERY'], $this->prettify());
        }

        $results = $statement->fetchAll(PDO::FETCH_OBJ);
        // filter output columns

        $sql = "SELECT COUNT(*) AS all_results FROM `role` $where";
        $statement = $db->prepare($sql);
        $statement->execute($filters);
        $all_results = (int) $statement->fetchObject()->all_results;

        $return = [
            'results' => $results,
            'all_results' => $all_results,
            'per_page' => $pagination['limit']
        ];
        return json_encode($return, $this->prettify());
    }

    public function post() {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }
        
        $data = $this->get_data();
        $columns = implode(', ', array_keys($data));
        $values = ':'.implode(', :', array_keys($data));
        $sql = "INSERT INTO role ($columns) VALUES ($values)";

        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute($data)) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_INSERT'], $this->prettify());
        }
    }

    public function put() {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = array_filter($this->get_data());
        if (!$data) {
            http_response_code(400);
            return json_encode(['error' => 'EMPTY_UPDATE'], $this->prettify());
        }

        $fields = [];
        foreach ($data as $column => $value) {
            $fields[] = "`$column` = :$column";
        }
        $set = implode(', ', $fields);
        $sql = "UPDATE `role` SET $set WHERE `id` = :id";

        $data['id'] = $this->input_int('id');

        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute($data)) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_UPDATE'], $this->prettify());
        }
    }

    public function delete() {
        if (!$this->validate_delete()) {
            http_response_code(400);
            return json_encode([
                'error' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $sql = "DELETE `role` WHERE `id` = :id";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute(['id' => $this->input_int('int')])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_DELETE'], $this->prettify());
        }
    }
}