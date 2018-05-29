<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\CouncilGrade\CouncilGradeMapper;
use Conselho\DataSource\Grade\GradeMapper;

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
            'council_id' => ['optional', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'grade_id' => ['optional', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
        ];

        return $this->run_validation($rules);
    }

    private function validate_post(): bool
    {
        $rules = [
            'council_id' => ['required', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'grade_id' => ['required', 'integer', ['min', 1], ['id_exists', GradeMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function has_conflict() : bool {
        $atlas = $this->atlas();
        $school_ids = [];

        if ($council_id = $this->input_int('council_id')) {
            $school_ids[] = $atlas->fetchRecord(CouncilMapper::class, $council_id)->school_id;
        }
        if ($grade_id = $this->input_int('grade_id')) {
            $school_ids[] = $atlas->fetchRecord(GradeMapper::class, $grade_id)->school_id;
        }

        return count(array_unique($school_ids)) > 1;
    }

    private function check_permission(int $id = null) : bool {
        $atlas = $this->atlas();

        if ($id) {
            $school_id = $atlas->fetchRecord($this->mapper_class_name, $id, ['council'])->council->school_id;
        } else {
            $school_id = $atlas->fetchRecord(CouncilMapper::class, $this->input_int('council_id'))->school_id;
        }

        return $this->has_permission('council', $school_id);
    }

    // METHODS

    public function get(): ?string
    {
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