<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\CouncilTopic\CouncilTopicMapper;

class CouncilTopic extends Controller
{
    public function __construct()
    {
        parent::__construct(CouncilTopicMapper::class);
    }

    private function get_post_data() : array {
        return [
            'council_id' => $this->input_int('council_id'),
            'topic_id' => $this->input_int('topic_id')
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'council_id' => ['optional', 'integer'],
            'grade_id' => ['optional', 'integer'],
            'search'  => ['optional', ['lengthMin', 3]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'integer'],
            'grade_id' => ['required', 'integer']
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
        $select = $atlas->select($this->mapper_class_name);
        if ($id = $this->input_int('id')) {
            $select->where('id = ?', $id);
        }
        if ($council_id = $this->input_int('council_id')) {
            $select->where('council_id = ?', $council_id);
        }
        if ($topic_id = $this->input_int('topic_id')) {
            $select->where('topic_id = ?', $topic_id);
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