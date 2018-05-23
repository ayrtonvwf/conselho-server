<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\StudentGrade\StudentGradeMapper;

class StudentGrade extends Controller
{
    public function __construct()
    {
        parent::__construct(StudentGradeMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'number >= ?' => $this->input_int('min_number'),
            'number <= ?' => $this->input_int('max_number'),
            'start_date >= ?' => $this->input_string('min_start_date'),
            'start_date <= ?' => $this->input_string('max_start_date'),
            'end_date >= ?' => $this->input_string('min_end_date'),
            'end_date <= ?' => $this->input_string('max_end_date'),
            'grade_id = ?' => $this->input_int('grade_id'),
            'student_id = ?' => $this->input_int('student_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'number' => $this->input_int('number'),
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id')
        ];
    }

    private function get_patch_data() : array {
        return [
            'number' => $this->input_int('number'),
            'start_date' => $this->input_string('start_date'),
            'end_date' => $this->input_string('end_date'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'min_number'  => ['optional', 'integer', ['min', 1]],
            'max_number'  => ['optional', 'integer', ['min', 1]],
            'min_start_date' => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'max_start_date' => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'min_end_date' => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'max_end_date' => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'grade_id' => ['optional', 'integer', ['min', 1]],
            'student_id' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'number' => ['required', 'integer', ['min', 1]],
            'start_date' => ['required', ['dateFormat', self::DATE_FORMAT]],
            'end_date' => ['required', ['dateFormat', self::DATE_FORMAT]],
            'grade_id' => ['required', 'integer', ['min', 1]],
            'student_id' => ['required', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'number' => ['optional', 'integer', ['min', 1]],
            'start_date' => ['optional', ['dateFormat', self::DATE_FORMAT]],
            'end_date' => ['optional', ['dateFormat', self::DATE_FORMAT]],
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