<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\MedicalReport\MedicalReportMapper;
use Conselho\DataSource\School\SchoolMapper;

class RoleType extends Controller
{
    public function __construct()
    {
        parent::__construct(MedicalReportMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'name LIKE ?' => $this->input_search('search'),
            'school_id = ?' => $this->input_int('school_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'name' => $this->input_string('name'),
            'school_id' => $this->input_int('school_id')
        ];
    }

    private function get_patch_data() : array {
        return [
            'name' => $this->input_string('name'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'search'  => ['optional', ['lengthBetween', 3, 50]],
            'school_id' => ['optional', 'integer', ['min', 1], ['id_exists', SchoolMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'name' => ['required', ['lengthBetween', 3, 50]],
            'school_id' => ['required', 'integer', ['min', 1], ['id_exists', SchoolMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'name' => ['required', ['lengthBetween', 3, 50]]
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

        $blocking_dependencies = ['roles'];

        if (!$this->delete_with_dependencies($record, $blocking_dependencies)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}