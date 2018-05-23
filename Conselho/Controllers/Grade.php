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

    private function get_get_data() : array {
        $where = array_filter([
            'school_id = ?' => $this->input_int('school_id'),
            'level >= ?' => $this->input_int('min_level'),
            'level <= ?' => $this->input_int('max_level'),
            'name LIKE ?' => $this->input_search('search')
        ]);
        if (!is_null($active = $this->input_bool('active'))) {
            $where['active = ?'] = $active;
        }
        return $where;
    }

    private function get_post_data() : array {
        return [
            'name' => $this->input_string('name'),
            'level' => $this->input_int('level'),
            'school_id' => $this->input_int('school_id'),
            'active' => $this->input_bool('active')
        ];
    }

    private function get_patch_data() : array {
        $data = array_filter([
            'name' => $this->input_string('name'),
            'level' => $this->input_int('level'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ]);
        if (!is_null($active = $this->input_bool('active'))) {
            $data['active'] = $active;
        }
        return $data;
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
            return $this->input_error_output();
        }

        $where = $this->get_get_data();

        $result = $this->search($where);

        return json_encode($result, $this->pretty());
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