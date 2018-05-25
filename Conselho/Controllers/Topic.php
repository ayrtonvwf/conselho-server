<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\Topic\TopicMapper;
use Conselho\DataSource\TopicOption\TopicOptionMapper;

class Topic extends Controller
{
    public function __construct()
    {
        parent::__construct(TopicMapper::class);
    }

    private function get_get_data() : array {
        $where = array_filter([
            'school_id = ?' => $this->input_int('school_id'),
            'topic_option_id = ?' => $this->input_int('topic_option_id'),
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
            'school_id' => $this->input_int('school_id')
        ];
    }
    private function get_patch_data() : array {
        $data = array_filter([
            'name' => $this->input_string('name'),
            'topic_option_id' => $this->input_int('topic_option'),
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
            'school_id' => ['optional', 'integer', ['min', 1], ['id_exists', SchoolMapper::class]],
            'topic_option_id' => ['optional', 'integer', ['min', 1], ['id_exists', TopicOptionMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'active' => ['required', 'boolean'],
            'name'  => ['required', ['lengthBetween', 3, 50]],
            'school_id' => ['required', 'integer', ['min', 1], ['id_exists', SchoolMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'active' => ['optional', 'boolean'],
            'name'  => ['optional', ['lengthBetween', 3, 50]],
            'topic_option_id' => ['required', 'integer', ['min', 1], ['id_exists', TopicOptionMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function has_conflict() : bool {
        $atlas = $this->atlas();
        $school_ids = [];

        if ($school_id = $this->input_int('school_id')) {
            $school_ids[] = $school_id;
        }
        if ($topic_option_id = $this->input_int('topic_option_id')) {
            $school_ids[] = $atlas->fetchRecord(TopicOptionMapper::class, $topic_option_id, ['topic'])->topic->school_id;
        }

        return count(array_unique($school_ids)) > 1;
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

        if ($this->has_conflict()) {
            http_response_code(409);
            return null;
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

        $blocking_dependencies = ['council_topics'];

        if (!$this->delete_with_dependencies($record, $blocking_dependencies)) {
            http_response_code(409);
            return;
        }

        http_response_code(204);
    }
}