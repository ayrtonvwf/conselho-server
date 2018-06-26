<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\MedicalReport\MedicalReportMapper;
use Conselho\DataSource\MedicalReportSubject\MedicalReportSubjectMapper;
use Conselho\DataSource\Subject\SubjectMapper;

class MedicalReportSubject extends Controller
{
    public function __construct()
    {
        parent::__construct(MedicalReportSubjectMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'medical_report_id = ?' => $this->input_int('medical_report_id'),
            'subject_id = ?' => $this->input_int('subject_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'medical_report_id' => $this->input_int('medical_report_id'),
            'subject_id' => $this->input_int('subject_id')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'medical_report_id' => ['optional', 'integer', ['min', 1], ['id_exists', MedicalReportMapper::class]],
            'subject_id' => ['optional', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'medical_report_id' => ['required', 'integer', ['min', 1], ['id_exists', MedicalReportMapper::class]],
            'subject_id' => ['required', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function has_conflict() : bool {
        $atlas = $this->atlas();
        $school_ids = [];

        if ($medical_report_id = $this->input_int('medical_report_id')) {
            $school_ids[] = $atlas->fetchRecord(MedicalReportMapper::class, $medical_report_id, ['student'])->student->school_id;
        }
        if ($subject_id = $this->input_int('subject_id')) {
            $school_ids[] = $atlas->fetchRecord(SubjectMapper::class, $subject_id)->school_id;
        }

        return count(array_unique($school_ids)) > 1;
    }

    private function check_permission(int $id = null) : bool {
        $atlas = $this->atlas();

        if ($id) {
            $school_id = $atlas->fetchRecord($this->mapper_class_name, $id, ['subject'])->subject->school_id;
        } else {
            $school_id = $atlas->fetchRecord(SubjectMapper::class, $this->input_int('subject_id'))->school_id;
        }

        return $this->has_permission('medical_report', $school_id);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return $this->input_error_output();
        }

        if ($this->has_conflict()) {
            http_response_code(409);
            return null;
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

        if ($this->has_conflict()) {
            http_response_code(409);
            return null;
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

    public function delete(int $id) : void {
        if (!$record = $this->fetch($id)) {
            http_response_code(404);
            return;
        }

        if (!$this->check_permission($id)) {
            http_response_code(403);
            return null;
        }

        if (!$this->delete_with_dependencies($record)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}