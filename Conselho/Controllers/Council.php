<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Council\CouncilMapper;

class Council extends Controller
{
    public function __construct()
    {
        parent::__construct(CouncilMapper::class);
    }

    private function get_post_data() : array {
        $now = date(self::DATETIME_INTERNAL_FORMAT);
        return [
            'name' => $this->input_string('name'),
            'active' => $this->input_bool('active'),
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'school_id' => $this->input_int('school_id'),
            'created_at' => $now,
            'updated_at' => $now
        ];
    }
    private function get_patch_data() : array {
        $now = date(self::DATETIME_INTERNAL_FORMAT);
        return [
            'name' => $this->input_string('name'),
            'active' => $this->input_bool('active'),
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'updated_at' => $now
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'integer'],
            'page' => ['optional', 'integer', ['min', 1]],
            'school_id' => ['optional', 'integer'],
            'active' => ['optional', 'integer', ['in', [0, 1]]],
            'search'  => ['optional', ['lengthMin', 3]],
            'min_start_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'max_start_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'min_end_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'max_end_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'min_created_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'max_created_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'min_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'max_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'active' => ['required', 'boolean'],
            'name'  => ['required', ['lengthBetween', 5, 30]],
            'start_date'  => ['required', ['dateFormat', self::DATE_FORMAT]],
            'end_date'  => ['required', ['dateFormat', self::DATE_FORMAT]],
            'school_id' => ['required', 'integer']
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'active' => ['optional', 'boolean'],
            'name'  => ['optional', ['lengthBetween', 5, 30]],
            'start_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'end_date'  => ['optional', ['dateFormat', self::DATE_FORMAT]],
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
        if ($search = $this->input_string('search')) {
            $select->where('name LIKE ?', "%$search%");
        }
        if ($min_start_date = $this->input_string('min_start_date')) {
            $select->where('start_date >= ?', $min_start_date);
        }
        if ($max_start_date = $this->input_string('max_start_date')) {
            $select->where('start_date <= ?', $max_start_date);
        }
        if ($min_end_date = $this->input_string('min_end_date')) {
            $select->where('end_date >= ?', $min_end_date);
        }
        if ($max_end_date = $this->input_string('max_end_date')) {
            $select->where('end_date <= ?', $max_end_date);
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

        $atlas = $this->atlas();

        $blocking_dependencies = ['evaluations', 'student_observations', 'grade_observations'];
        $record = $atlas->fetchRecord($this->mapper_class_name, $record->id, $blocking_dependencies);
        $has_blocking_dependency = array_filter($blocking_dependencies, function($dependency) use ($record) {
            return (bool) $record->$dependency;
        });
        if ($has_blocking_dependency) {
            http_response_code(409);
            return;
        }

        $full_dependencies = array_merge($blocking_dependencies, ['council_topics', 'council_grades']);
        $record = $atlas->fetchRecord($this->mapper_class_name, $record->id, $full_dependencies);
        $transaction = $atlas->newTransaction();
        foreach ($full_dependencies as $dependency_name) {
            foreach ($record->$dependency_name as $dependency) {
                $transaction->delete($dependency);
            }
        }
        $transaction->delete($record);
        if (!$transaction->exec()) {
            http_response_code(500);
            return;
        }

        http_response_code(204);
    }
}