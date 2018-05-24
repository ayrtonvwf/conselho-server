<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Topic\TopicMapper;
use Conselho\DataSource\TopicOption\TopicOptionMapper;

class TopicOption extends Controller
{
    public function __construct()
    {
        parent::__construct(TopicOptionMapper::class);
    }

    private function get_get_data() : array {
        $where = array_filter([
            'value >= ?' => $this->input_int('min_value'),
            'value <= ?' => $this->input_int('max_value'),
            'topic_id = ?' => $this->input_int('topic_id'),
            'name LIKE ?' => $this->input_search('search')
        ]);
        if (!is_null($active = $this->input_bool('active'))) {
            $where['active = ?'] = $active;
        }
        return $where;
    }

    private function get_post_data() : array {
        return [
            'active' => $this->input_bool('active'),
            'name' => $this->input_string('name'),
            'value' => $this->input_int('value'),
            'topic_id' => $this->input_int('topic_id')
        ];
    }
    private function get_patch_data() : array {
        $data = array_filter([
            'name' => $this->input_string('name'),
            'value' => $this->input_int('value'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ]);
        if (!is_null($active = $this->input_bool('active'))) {
            $data['active'] = $active;
        }
        return $data;
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'active' => ['optional', 'integer', ['in', [0, 1]]],
            'search'  => ['optional', ['lengthBetween', 3, 50]],
            'min_value' => ['optional', 'integer', ['min', 0], ['max', 100]],
            'max_value' => ['optional', 'integer', ['min', 0], ['max', 100]],
            'topic_id' => ['optional', 'integer', ['min', 1], ['id_exists', TopicMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'active' => ['required', 'boolean'],
            'name'  => ['required', ['lengthBetween', 3, 50]],
            'value' => ['required', 'integer', ['min', 0], ['max', 100]],
            'topic_id' => ['required', 'integer', ['min', 1], ['id_exists', TopicMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'active' => ['optional', 'boolean'],
            'name'  => ['optional', ['lengthBetween', 3, 50]],
            'value' => ['required', 'integer', ['min', 0], ['max', 100]]
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

        $blocking_dependencies = ['evaluations'];

        if (!$this->delete_with_dependencies($record, $blocking_dependencies)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}