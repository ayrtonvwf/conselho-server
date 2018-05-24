<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\CouncilTopic\CouncilTopicMapper;
use Conselho\DataSource\Topic\TopicMapper;

class CouncilTopic extends Controller
{
    public function __construct()
    {
        parent::__construct(CouncilTopicMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'council_id = ?' => $this->input_int('council_id'),
            'topic_id = ?' => $this->input_int('topic_id')
        ]);
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
            'council_id' => ['optional', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'topic_id' => ['optional', 'integer', ['min', 1], ['id_exists', TopicMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'topic_id' => ['required', 'integer', ['min', 1], ['id_exists', TopicMapper::class]]
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

        if (!$this->delete_with_dependencies($record)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}