<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Grade\GradeMapper;

class Grade extends Controller
{
    public function __construct()
    {
        parent::__construct(GradeMapper::class);
    }

    private function get_post_data() : array {
        $now = date(self::DATETIME_INTERNAL_FORMAT);
        return [
            'name' => $this->input_string('name'),
            'level' => $this->input_int('level'),
            'school_id' => $this->input_int('school_id'),
            'active' => $this->input_bool('active'),
            'created_at' => $now,
            'updated_at' => $now
        ];
    }

    private function get_patch_data() : array {
        return [
            'name' => $this->input_string('name'),
            'level' => $this->input_int('level'),
            'active' => $this->input_int('active'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'active' => ['optional', 'integer', ['in', [0, 1]]],
            'min_level' => ['optional', 'integer', ['min', 1]],
            'max_level' => ['optional', 'integer', ['min', 1]],
            'search'  => ['optional', ['lengthMin', 3]],
            'school_id' => ['optional', 'integer']
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'name'  => ['required', ['lengthBetween', 1, 50]],
            'level'  => ['required', 'integer', ['min', 1]],
            'active' => ['required', 'boolean'],
            'school_id' => ['required', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'name'  => ['optional', ['lengthBetween', 1, 50]],
            'level'  => ['required', 'integer', ['min', 1]],
            'active' => ['required', 'boolean']
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
        $select = $atlas->select($this->mapper_class_name);
        if ($id = $this->input_int('id')) {
            $select->where('id = ?', $id);
        }
        if ($school_id = $this->input_int('school_id')) {
            $select->where('school_id = ?', $school_id);
        }
        if (!is_null($active = $this->input_bool('active'))) {
            $select->where('active = ?', $active);
        }
        if ($min_level = $this->input_int('min_level')) {
            $select->where('level >= ?', $min_level);
        }
        if ($max_level = $this->input_int('max_level')) {
            $select->where('level <= ?', $max_level);
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
            $select->where('name LIKE ?', "%$search%");
        }
        $pagination = $this->get_pagination();
        $select->limit($pagination['limit']);
        $select->offset($pagination['offset']);
        $select->cols(['*']);

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
        if (!$record = $this->insert($data)) {
            http_response_code(500);
            return null;
        }

        return $this->post_output($record);
    }

    public function patch(int $id) : ?string {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return null;
        }

        if (!$this->validate_patch()) {
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

    public function delete(int $id) : void {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return;
        }

        if (!$this->atlas()->delete($record)) {
            http_response_code(500);
            return;
        }

        http_response_code(204);
    }

}