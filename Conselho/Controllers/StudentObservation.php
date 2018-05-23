<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;

class StudentObservation extends Controller
{
    public function __construct()
    {
        parent::__construct(StudentObservationMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'description LIKE ?' => $this->input_search('search'),
            'council_id = ?' => $this->input_int('council_id'),
            'grade_id = ?' => $this->input_int('grade_id'),
            'student_id = ?' => $this->input_int('student_id'),
            'subject_id = ?' => $this->input_int('subject_id'),
            'user_id = ?' => $this->input_int('user_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'description' => $this->input_string('description'),
            'council_id' => $this->input_int('council_id'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id'),
            'subject_id' => $this->input_int('subject_id'),
            'user_id' => $this->get_user()->id
        ];
    }

    private function get_patch_data() : array {
        return [
            'description' => $this->input_string('description'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'search'  => ['optional', ['lengthMin', 3]],
            'council_id'  => ['optional', 'integer', ['min', 1]],
            'grade_id'  => ['optional', 'integer', ['min', 1]],
            'student_id' => ['optional', 'integer', ['min', 1]],
            'subject_id' => ['optional', 'integer', ['min', 1]],
            'user_id' => ['optional', 'integer', ['min', 1]],
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'description' => ['required', ['lengthMin', 3]],
            'council_id' => ['required', 'integer', ['min', 1]],
            'grade_id' => ['required', 'integer', ['min', 1]],
            'student_id' => ['required', 'integer', ['min', 1]],
            'subject_id' => ['required', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'description' => ['required', ['lengthMin', 3]]
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