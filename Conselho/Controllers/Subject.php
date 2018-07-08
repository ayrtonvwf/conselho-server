<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\Subject\SubjectMapper;

class Subject extends Controller
{
    public function __construct()
    {
        parent::__construct(SubjectMapper::class);
    }

    private function get_get_data() : array {
        $where = array_filter([
            'school_id = ?' => $this->input_int('school_id'),
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
            'active' => $this->input_bool('active'),
            'school_id' => $this->input_int('school_id')
        ];
    }
    private function get_patch_data() : array {
        $data = array_filter([
            'name' => $this->input_string('name'),
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
            'school_id' => ['optional', 'integer', ['min', 1], ['id_exists', SchoolMapper::class]],
            'active' => ['optional', 'integer', ['in', [0, 1]]],
            'search'  => ['optional', ['lengthBetween', 3, 50]],
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'active' => ['required', 'is_bool'],
            'name'  => ['required', ['lengthBetween', 3, 50]],
            'school_id' => ['required', 'integer', ['min', 1], ['id_exists', SchoolMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'active' => ['optional', 'is_bool'],
            'name'  => ['optional', ['lengthBetween', 3, 50]],
        ];

        return $this->run_validation($rules);
    }

    private function check_permission(int $id = null) : bool {
        if ($id) {
            $school_id = $this->fetch($id)->school_id;
        } else {
            $school_id = $this->input_int('school_id');
        }

        return $this->has_permission('subject', $school_id);
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

        if (!$this->check_permission()) {
            http_response_code(403);
            return null;
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

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return null;
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

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return;
        }

        $blocking_dependencies = ['evaluations', 'student_observations', 'grade_observations'];

        if (!$this->delete_with_dependencies($record, $blocking_dependencies)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}