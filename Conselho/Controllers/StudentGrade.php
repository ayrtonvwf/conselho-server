<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class StudentGrade extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id'),
            'min_number' => $this->input_int('min_number'),
            'max_number' => $this->input_int('max_number'),
            'min_start_date' => $this->input_string('min_start_date'),
            'max_start_date' => $this->input_string('max_start_date'),
            'min_end_date' => $this->input_string('min_end_date'),
            'max_end_date' => $this->input_string('max_end_date'),
            'min_updated_at' => $this->input_string('min_updated_at'),
            'max_updated_at' => $this->input_string('max_updated_at')
        ];
        return array_filter($filters);
    }

    private function get_data() : array {
        return     [
            'grade_id' => $this->input_int('grade_id'),
            'number' => $this->input_int('number'),
            'student_id' => $this->input_int('student_id'),
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'integer'],
            'grade_id' => ['optional', 'integer'],
            'student_id' => ['optional', 'integer'],
            'min_number' => ['optional', 'integer', ['min', 1]],
            'max_number' => ['optional', 'integer', ['min', 1]],
            'max_start'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_start'  => ['optional', ['dateFormat', 'Y-m-d']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'grade_id' => ['required', 'integer'],
            'number' => ['required', 'integer', ['min', 1]],
            'student_id' => ['required', 'integer'],
            'start_date' => ['required', ['dateFormat', 'Y-m-d']],
            'end_date' => ['required', ['dateFormat', 'Y-m-d']]
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'integer'],
            'grade_id' => ['optional', 'integer'],
            'number' => ['optional', 'integer', ['min', 1]],
            'student_id' => ['optional', 'integer'],
            'start_date' => ['optional', ['dateFormat', 'Y-m-d']],
            'end_date' => ['optional', ['dateFormat', 'Y-m-d']]
        ];

        return $this->run_validation($rules);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'objectId', 'inCollection']
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
            ], $this->prettify());
        }

        $filters = $this->get_filters();

        $where = [];
        if (isset($filters['id'])) {
            $where[] = '`id` = :id';
        }
        if (isset($filters['grade_id'])) {
            $where[] = '`grade_id` = :grade_id';
        }
        if (isset($filters['student_id'])) {
            $where[] = '`student_id` = :student_id';
        }
        if (isset($filters['max_number'])) {
            $where[] = '`number` <= :max_number';
        }
        if (isset($filters['min_number'])) {
            $where[] = '`number` >= :min_number';
        }
        if (isset($filters['max_start_date'])) {
            $where[] = '`start_date` <= :max_start_date';
        }
        if (isset($filters['min_start_date'])) {
            $where[] = '`start_date` >= :min_start_date';
        }
        if (isset($filters['max_end_date'])) {
            $where[] = '`end_date` <= :max_end_date';
        }
        if (isset($filters['min_end_date'])) {
            $where[] = '`end_date` >= :min_end_date';
        }
        if (isset($filters['max_updated_at'])) {
            $where[] = '`updated_at` <= :max_updated_at';
        }
        if (isset($filters['min_updated_at'])) {
            $where[] = '`updated_at` >= :min_updated_at';
        }

        $where = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $pagination = $this->get_pagination();

        $sql = "SELECT * FROM `student_grade` $where LIMIT :limit OFFSET :offset";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);

        $parameters = $filters + $pagination;
        foreach ($parameters as $parameter_name => $parameter_value) {
            $statement->bindValue(":$parameter_name", $parameter_value, is_int($parameter_value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        if (!$statement->execute()) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_QUERY'], $this->prettify());
        }

        $results = $statement->fetchAll(PDO::FETCH_OBJ);
        // filter output columns

        $sql = "SELECT COUNT(*) AS `all_results` FROM `student_grade` $where";
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

    public function post() : string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = $this->get_data();
        $columns = implode(', ', array_keys($data));
        $values = ':'.implode(', :', array_keys($data));
        $sql = "INSERT INTO `student_grade` ($columns) VALUES ($values)";

        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute($data)) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_INSERT'], $this->prettify());
        }
        return json_encode(['error_code' => null], $this->prettify());
    }

    public function put() : string {
        if (!$this->validate_put()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $data = array_filter($this->get_data());
        if (!$data) {
            http_response_code(400);
            return json_encode(['error_code' => 'EMPTY_UPDATE'], $this->prettify());
        }

        $fields = [];
        foreach ($data as $column => $value) {
            $fields[] = "`$column` = :$column";
        }
        $set = implode(', ', $fields);
        $sql = "UPDATE `student_grade` SET $set WHERE `id` = :id";

        $data['id'] = $this->input_int('id');

        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute($data)) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_UPDATE'], $this->prettify());
        }
        return json_encode(['error_code' => null], $this->prettify());
    }

    public function delete() : string {
        if (!$this->validate_delete()) {
            http_response_code(400);
            return json_encode([
                'error_code' => 'INVALID_INPUT',
                'error_messages' => $this->get_validation_errors()
            ], $this->prettify());
        }

        $sql = "DELETE FROM `student_grade` WHERE `id` = :id";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute(['id' => $this->input_int('integer')])) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_DELETE'], $this->prettify());
        }
        return json_encode(['error_code' => null], $this->prettify());
    }
}