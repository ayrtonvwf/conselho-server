<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Evaluation\EvaluationMapper;

class Evaluation extends Controller
{
    private function get_post_data() : array {
        $now = date(self::DATETIME_INTERNAL_FORMAT);
        return [
            'council_id' => $this->input_int('council_id'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id'),
            'subject_id' => $this->input_int('subject_id'),
            'topic_option_id' => $this->input_int('topic_option_id'),
            'user_id' => $this->get_user()->id,
            'created_at' => $now,
            'updated_at' => $now
        ];
    }

    private function get_patch_data() : array {
        return [
            'topic_option_id' => $this->input_int('topic_option_id'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = [
            'id' => ['optional', 'integer'],
            'council_id' => ['optional', 'integer'],
            'grade_id' => ['optional', 'integer'],
            'student_id' => ['optional', 'integer'],
            'subject_id' => ['optional', 'integer'],
            'topic_option_id' => ['optional', 'integer'],
            'user_id' => ['optional', 'integer'],
            'min_created_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'max_created_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'min_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'max_updated_at'  => ['optional', ['dateFormat', self::DATETIME_EXTERNAL_FORMAT]],
            'page' => ['optional', 'integer', ['min', 1]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'integer'],
            'grade_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'subject_id' => ['required', 'integer'],
            'topic_option_id' => ['required', 'integer']
        ];

        return $this->run_validation($rules);
    }
    private function validate_patch() : bool {
        $rules = [
            'topic_option_id' => ['required', 'integer']
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $atlas = $this->atlas();
        $select = $atlas->select(EvaluationMapper::CLASS);
        if ($id = $this->input_int('id')) {
            $select->where('id = ?', $id);
        }
        if ($council_id = $this->input_int('council_id')) {
            $select->where('council_id = ?', $council_id);
        }
        if ($grade_id = $this->input_int('grade_id')) {
            $select->where('grade_id = ?', $grade_id);
        }
        if ($student_id = $this->input_int('student_id')) {
            $select->where('student_id = ?', $student_id);
        }
        if ($subject_id = $this->input_int('subject_id')) {
            $select->where('subject_id = ?', $subject_id);
        }
        if ($topic_option_id = $this->input_int('topic_option_id')) {
            $select->where('topic_option_id = ?', $topic_option_id);
        }
        if ($min_created_at = $this->input_datetime('min_created_at')) {
            $select->where('created_at >= ?', $min_created_at);
        }
        if ($max_created_at = $this->input_datetime('max_created_at')) {
            $select->where('created_at <= ?', $max_created_at);
        }
        if ($min_updated_at = $this->input_datetime('min_updated_at')) {
            $select->where('updated_at >= ?', $min_updated_at);
        }
        if ($max_updated_at = $this->input_datetime('max_updated_at')) {
            $select->where('updated_at <= ?', $max_updated_at);
        }
        $pagination = $this->get_pagination();
        $select->limit($pagination['limit']);
        $select->offset($pagination['offset']);
        $select->cols(['*']);

        $results = array_map(function($result) {
            $result['created_at'] = $this->output_datetime($result['created_at']);
            $result['updated_at'] = $this->output_datetime($result['updated_at']);
            return $result;
        }, $select->fetchAll());

        $return = [
            'total_results' => $select->fetchCount(),
            'current_page' => $pagination['page'],
            'max_results_per_page' => $pagination['limit'],
            'results' => $results
        ];
        return json_encode($return, $this->pretty());
    }

    public function post() : ?string {
        if (!$this->validate_post()) {
            http_response_code(400);
            return json_encode([
                'input_errors' => $this->get_validation_errors()
            ], $this->pretty());
        }

        $atlas = $this->atlas();
        $data = $this->get_post_data();
        $evaluation = $atlas->newRecord(EvaluationMapper::CLASS, $data);
        if (!$atlas->insert($evaluation)) {
            http_response_code(500);
            return null;
        }

        return json_encode(['id' => $evaluation->id, 'created_at' => $evaluation->created_at], $this->pretty());
    }
    public function patch(int $id) : ?string {
        $atlas = $this->atlas();
        $evaluation = $atlas->fetchRecord(EvaluationMapper::CLASS, $id);
        if (!$evaluation) {
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
        $evaluation->set($data);
        if (!$atlas->update($evaluation)) {
            http_response_code(500);
            return null;
        }

        return json_encode(['id' => $evaluation->id, 'created_at' => $evaluation->created_at], $this->pretty());
    }

    public function delete(int $id) : void {
        $atlas = $this->atlas();
        $evaluation = $atlas->fetchRecord(EvaluationMapper::CLASS, $id);
        if (!$evaluation) {
            http_response_code(404);
            return;
        }

        if (!$atlas->delete($evaluation)) {
            http_response_code(500);
            return;
        }
        http_response_code(204);
        return;
    }
}