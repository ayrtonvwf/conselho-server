<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\MedicalReportSubject\MedicalReportSubjectMapper;

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
            'medical_report_id' => ['optional', 'integer', ['min', 1]],
            'subject_id' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'medical_report_id' => ['required', 'integer', ['min', 1]],
            'subject_id' => ['required', 'integer', ['min', 1]]
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