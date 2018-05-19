<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class Council extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'min_start_date' => $this->input_string('min_start_date'),
            'max_start_date' => $this->input_string('max_start_date'),
            'min_end_date' => $this->input_string('min_end_date'),
            'max_end_date' => $this->input_string('max_end_date'),
            'min_updated_at' => $this->input_string('min_updated_at'),
            'max_updated_at' => $this->input_string('max_updated_at'),
            'school_id' => $this->input_int('school_id'),
            'search' => $this->input_string('search')
        ];
        return array_filter($filters);
    }

    private function get_data() : array {
        return [
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'name' => $this->input_string('name'),
            'school_id' => $this->input_int('school_id')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'integer'],
            'school_id' => ['optional', 'integer'],
            'max_start_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_start_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'max_end_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_end_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'start_date'  => ['required', ['dateFormat', 'Y-m-d']],
            'end_date'  => ['required', ['dateFormat', 'Y-m-d']],
            'name'  => ['required', ['lengthBetween', 5, 30]],
            'school_id' => ['required', 'integer'],
            'topic_ids' => ['required', 'array']
        ];

        return $this->run_validation($rules);
    }

    private function validate_put() : bool {
        $rules = [
            'id' => ['required', 'integer'],
            'start_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'end_date'  => ['optional', ['dateFormat', 'Y-m-d']],
            'name'  => ['optional', ['lengthBetween', 5, 30]],
            'school_id' => ['optional', 'integer'],
            'topic_ids' => ['optional', 'array']
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
        if (isset($filters['school_id'])) {
            $where[] = '`school_id` = :school_id';
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
        if (isset($filters['search'])) {
            $where[] = '`name` LIKE %:search%';
        }

        $where = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $pagination = $this->get_pagination();

        $sql = "SELECT * FROM `council` $where LIMIT :limit OFFSET :offset";
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

        $sql = "SELECT COUNT(*) AS `all_results` FROM `council` $where";
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

        $sql = "INSERT INTO `council` (name, start_date, end_date, school_id) VALUES (:name, :start_date, :end_date, :school_id)";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        $statement->bindValue(':name', $this->input_string('name'), PDO::PARAM_STR);
        $statement->bindValue(':start_date', $this->input_string('start_date'), PDO::PARAM_STR);
        $statement->bindValue(':end_date', $this->input_string('end_date'), PDO::PARAM_STR);
        $statement->bindValue(':school_id', $this->input_string('school_id'), PDO::PARAM_INT);

        if (!$statement->execute()) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_INSERT'], $this->pretty());
        }

        $council_id = $db->lastInsertId();
        $values = [];
        $topic_ids = $this->input_raw('topic_ids');
        foreach ($topic_ids as $k => $topic_id) {
            $values[] = "(:council_id_$k, :topic_id_$k)";
        }
        $values = implode(', ', $values);
        $sql = "INSERT INTO `council_topic` (council_id, topic_id) VALUES $values";
        $statement = $db->prepare($sql);
        foreach ($topic_ids as $k => $topic_id) {
            $statement->bindValue(":council_id_$k", $council_id, PDO::PARAM_INT);
            $statement->bindValue(":topic_id_$k", $topic_id, PDO::PARAM_INT);
        }

        if (!$statement->execute()) {
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
        $sql = "UPDATE `council` SET $set WHERE `id` = :id";

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

        $sql = "DELETE FROM `council` WHERE `id` = :id";
        $db = $this->get_db_connection();
        $statement = $db->prepare($sql);
        if (!$statement->execute(['id' => $this->input_int('integer')])) {
            http_response_code(500);
            return json_encode(['error_code' => 'CANNOT_DELETE'], $this->pretty());
        }
        return json_encode(['error_code' => null], $this->pretty());
    }
}