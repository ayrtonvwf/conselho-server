<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class GradeObservation extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'min_updated_at' => $this->input_string('min_updated_at'),
            'max_updated_at' => $this->input_string('max_updated_at'),
            'council_id' => $this->input_int('council_id'),
            'user_id' => $this->input_int('user_id'),
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'search' => $this->input_string('search')
        ];
        return array_filter($filters);
    }

    private function get_data() : array {
        return [
            'council_id' => $this->input_int('council_id'),
            'user_id' => $this->input_int('user_id'),
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'description' => $this->input_string('description')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'integer'],
            'council_id' => ['optional', 'integer'],
            'user_id' => ['optional', 'integer'],
            'grade_id' => ['optional', 'integer'],
            'subject_id' => ['optional', 'integer'],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'grade_id' => ['required', 'integer'],
            'subject_id' => ['required', 'integer'],
            'description' => ['required', 'string', ['maxLength', 300]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'integer'],
            'council_id' => ['optional', 'integer'],
            'user_id' => ['optional', 'integer'],
            'grade_id' => ['optional', 'integer'],
            'subject_id' => ['optional', 'integer'],
            'description' => ['optional', 'string', ['maxLength', 300]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'integer']
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
        if (isset($filters['council_id'])) {
            $where[] = '`council_id` = :council_id';
        }
        if (isset($filters['user_id'])) {
            $where[] = '`user_id` = :user_id';
        }
        if (isset($filters['grade_id'])) {
            $where[] = '`grade_id` = :grade_id';
        }
        if (isset($filters['subject_id'])) {
            $where[] = '`subject_id` = :subject_id';
        }
        if (isset($filters['max_updated_at'])) {
            $where[] = '`updated_at` <= :max_updated_at';
        }
        if (isset($filters['min_updated_at'])) {
            $where[] = '`updated_at` >= :min_updated_at';
        }
        if (isset($filters['search'])) {
            $where[] = '`description` LIKE %:search%';
        }

        $where = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $pagination = $this->get_pagination();

        $sql = "SELECT * FROM `grade_observation` $where LIMIT :limit OFFSET :offset";
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

        $sql = "SELECT COUNT(*) AS `all_results` FROM `grade_observation` $where";
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

    public function post() : string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $data = $this->get_data();
        $columns = implode(', ', array_keys($data));
        $values = ':'.implode(', :', array_keys($data));
        $sql = "INSERT INTO `grade_observation` ($columns) VALUES ($values)";

        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute($data)) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_INSERT'], $this->pretty());
        }
        return json_encode(['error_code' => null], $this->pretty());
    }

    public function put() : string {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $data = array_filter($this->get_data());
        if (!$data) {
            http_response_code(400);
            return json_encode(['error_code' => 'EMPTY_UPDATE'], $this->pretty());
        }

        $fields = [];
        foreach ($data as $column => $value) {
            $fields[] = "`$column` = :$column";
        }
        $set = implode(', ', $fields);
        $sql = "UPDATE `grade_observation` SET $set WHERE `id` = :id";

        $data['id'] = $this->input_int('id');

        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute($data)) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_UPDATE'], $this->pretty());
        }
        return json_encode(['error_code' => null], $this->pretty());
    }

    public function delete() : string {
        if (!$this->validate_delete()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $sql = "DELETE FROM `grade_observation` WHERE `id` = :id";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute(['id' => $this->input_int('integer')])) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_DELETE'], $this->pretty());
        }
        return json_encode(['error_code' => null], $this->pretty());
    }

}