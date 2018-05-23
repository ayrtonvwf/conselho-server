<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\CouncilGrade\CouncilGradeMapper;

class CouncilGrade extends Controller
{
    public function __construct()
    {
        parent::__construct(CouncilGradeMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'council_id = ?' => $this->input_int('council_id'),
            'grade_id = ?' => $this->input_int('grade_id')
        ]);
    }

    private function get_post_data(): array
    {
        return [
            'council_id' => $this->input_int('council_id'),
            'grade_id' => $this->input_int('grade_id')
        ];
    }

    // VALIDATION

    private function validate_get(): bool
    {
        $rules = self::DEFAULT_GET_RULES + [
                'council_id' => ['optional', 'integer'],
                'grade_id' => ['optional', 'integer'],
                'search' => ['optional', ['lengthMin', 3]]
            ];

        return $this->run_validation($rules);
    }

    private function validate_post(): bool
    {
        $rules = [
            'council_id' => ['required', 'integer'],
            'grade_id' => ['required', 'integer']
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get(): string
    {
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