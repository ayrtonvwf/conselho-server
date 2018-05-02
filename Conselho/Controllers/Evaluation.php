<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class Evaluation extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'user_id' => $this->input_int('user_id'),
            'student_id' => $this->input_int('student_id'),
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'council_id' => $this->input_int('council_id'),
            'topic_id' => $this->input_int('topic_id'),
            'min_updated_at' => $this->input_string('min_updated_at'),
            'max_updated_at' => $this->input_string('max_updated_at')
        ];
        $filters = array_filter($filters);
        if (!is_null($this->input_int('min_value'))) {
            $filters['min_value'] = $this->input_int('min_value');
        }
        if (!is_null($this->input_int('max_value'))) {
            $filters['max_value'] = $this->input_int('max_value');
        }
        return $filters;
    }

    private function get_data() : array {
        return [
            'user_id' => $this->input_int('user_id'),
            'student_id' => $this->input_int('student_id'),
            'grade_id' => $this->input_int('grade_id'),
            'subject_id' => $this->input_int('subject_id'),
            'council_id' => $this->input_int('council_id'),
            'topic_id' => $this->input_int('topic_id'),
            'value' => $this->input_int('value')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'int'],
            'user_id' => ['optional', 'int'],
            'student_id' => ['optional', 'int'],
            'grade_id' => ['optional', 'int'],
            'subject_id' => ['optional', 'int'],
            'council_id' => ['optional', 'int'],
            'topic_id' => ['optional', 'int'],
            'min_value' => ['optional'],
            'max_value' => ['optional'],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'user_id' => ['required', 'int'],
            'student_id' => ['required', 'int'],
            'grade_id' => ['required', 'int'],
            'subject_id' => ['required', 'int'],
            'council_id' => ['required', 'int'],
            'topic_id' => ['required', 'int'],
            'value' => ['required']
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'int'],
            'user_id' => ['optional', 'int'],
            'student_id' => ['optional', 'int'],
            'grade_id' => ['optional', 'int'],
            'subject_id' => ['optional', 'int'],
            'council_id' => ['optional', 'int'],
            'topic_id' => ['optional', 'int'],
            'value' => ['optional']
        ];

        return $this->run_validation($rules);
    }

    private function validate_delete() : bool {
        $rules = [
            'id' => ['required', 'int']
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
        if (isset($filters['student_id'])) {
            $where[] = '`student_id` = :student_id';
        }
        if (isset($filters['grade_id'])) {
            $where[] = '`grade_id` = :grade_id';
        }
        if (isset($filters['subject_id'])) {
            $where[] = '`subject_id` = :subject_id';
        }
        if (isset($filters['council_id'])) {
            $where[] = '`council_id` = :council_id';
        }
        if (isset($filters['topic_id'])) {
            $where[] = '`topic_id` = :topic_id';
        }
        if (isset($filters['max_updated_at'])) {
            $where[] = '`updated_at` <= :max_updated_at';
        }
        if (isset($filters['min_updated_at'])) {
            $where[] = '`updated_at` >= :min_updated_at';
        }
        if (isset($filters['min_value'])) {
            $where[] = '`value` >= :min_value';
        }
        if (isset($filters['max_value'])) {
            $where[] = '`value` <= :max_value';
        }

        $where = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $pagination = $this->get_pagination();

        $sql = "SELECT * FROM `evaluation` $where LIMIT :limit OFFSET :offset";
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

        $sql = "SELECT COUNT(*) AS `all_results` FROM `evaluation` $where";
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
        $sql = "INSERT INTO `evaluation` ($columns) VALUES ($values)";

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
        $sql = "UPDATE `evaluation` SET $set WHERE `id` = :id";

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

        $sql = "DELETE FROM `evaluation` WHERE `id` = :id";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute(['id' => $this->input_int('int')])) {
            http_response_code(500);
            return json_encode(['error' => 'CANNOT_DELETE'], $this->prettify());
        }
    }

}