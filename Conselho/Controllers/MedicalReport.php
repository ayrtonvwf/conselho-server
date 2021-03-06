<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\MedicalReport\MedicalReportMapper;
use Conselho\DataSource\Student\StudentMapper;

class MedicalReport extends Controller
{
    public function __construct()
    {
        parent::__construct(MedicalReportMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'student_id = ?' => $this->input_int('student_id'),
            'description LIKE ?' => $this->input_search('search')
        ]);
    }

    private function get_post_data() : array {
        return [
            'description' => $this->input_string('description'),
            'student_id' => $this->input_int('student_id')
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
            'search'  => ['optional', ['lengthBetween', 3, 50]],
            'student_id' => ['optional', 'integer', ['min', 1], ['id_exists', StudentMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'description' => ['required', ['lengthBetween', 3, 50]],
            'student_id' => ['required', 'integer', ['min', 1], ['id_exists', StudentMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'description' => ['required', ['lengthBetween', 3, 50]]
        ];

        return $this->run_validation($rules);
    }

    private function check_permission(int $id = null) : bool {
        $atlas = $this->atlas();

        if ($id) {
            $student = $atlas->fetchRecord($this->mapper_class_name, $id, ['student' => ['student_grades' => ['grade']]])->student;
        } else {
            $student = $atlas->fetchRecord(StudentMapper::class, $this->input_int('id'), ['student_grades' => ['grade']]);
        }

        $current_student_grades = array_filter($student->student_grades->getArrayCopy(), function($student_grade) {
            return $student_grade['end_date'] >= date(self::DATE_FORMAT) &&
                $student_grade['start_date'] <= date(self::DATE_FORMAT);
        });
        $current_student_grade = $current_student_grades ? $current_student_grades[array_key_first($current_student_grades)] : null;
        if (!$current_student_grade) {
            return false;
        }

        $school_id = $current_student_grade->grade->school_id;

        return $this->has_permission('medical_report', $school_id);
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

        if (!$this->check_permission()) {
            http_response_code(403);
            return null;
        }

        return $this->post_output($record);
    }

    public function patch(int $id) : ?string {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return null;
        }

        if (!$this->check_permission($id)) {
            http_response_code(403);
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

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return;
        }

        if (!$this->delete_with_dependencies($record)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }

}