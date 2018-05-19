<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use PDO;

class RoleType extends Controller
{
    private function get_filters() : array {
        $filters = [
            'id' => $this->input_int('id'),
            'search' => $this->input_string('search'),
            'min_updated_at' => $this->input_string('min_updated_at'),
            'max_updated_at' => $this->input_string('max_updated_at')
        ];
        return array_filter($filters);
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'integer'],
            'max_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'min_updated_at'  => ['optional', ['dateFormat', 'Y-m-d']],
            'search'  => ['optional', ['lengthMin', 3]],
            'page' => ['optional', 'integer', ['min', 1]]
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
            $where[] = '`name` LIKE %:search%';
        }

        $where = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $pagination = $this->get_pagination();

        $sql = "SELECT * FROM `role_type` $where LIMIT :limit OFFSET :offset";
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

        $sql = "SELECT COUNT(*) AS `all_results` FROM `role_type` $where";
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

}