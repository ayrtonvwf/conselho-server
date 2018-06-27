<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Student\StudentMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\TopicOption\TopicOptionMapper;
use Conselho\DataSource\User\UserMapper;

class Evaluation extends Controller
{
    public function __construct()
    {
        parent::__construct(EvaluationMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'council_id = ?' => $this->input_int('council_id'),
            'grade_id = ?' => $this->input_int('grade_id'),
            'student_id = ?' => $this->input_int('student_id'),
            'subject_id = ?' => $this->input_int('subject_id'),
            'topic_option_id = ?' => $this->input_int('topic_option_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'council_id' => $this->input_int('council_id'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id'),
            'subject_id' => $this->input_int('subject_id'),
            'topic_option_id' => $this->input_int('topic_option_id'),
            'user_id' => $this->get_user()->id
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
        $rules = self::DEFAULT_GET_RULES + [
            'council_id' => ['optional', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'grade_id' => ['optional', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'student_id' => ['optional', 'integer', ['min', 1], ['id_exists', StudentMapper::class]],
            'subject_id' => ['optional', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'topic_option_id' => ['optional', 'integer', ['min', 1], ['id_exists', TopicOptionMapper::class]],
            'user_id' => ['optional', 'integer', ['min', 1], ['id_exists', UserMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'council_id' => ['required', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'grade_id' => ['required', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'student_id' => ['required', 'integer', ['min', 1], ['id_exists', StudentMapper::class]],
            'subject_id' => ['required', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'topic_option_id' => ['required', 'integer', ['min', 1], ['id_exists', TopicOptionMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'topic_option_id' => ['required', 'integer', ['min', 1], ['id_exists', TopicOptionMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function has_conflict(int $id = null) : bool {
        $atlas = $this->atlas();
        if ($id) { // patch
            $evaluation = $atlas->fetchRecord(EvaluationMapper::class, $id, ['topic_option' => ['topic']]);
            $topic_option = $atlas->fetchRecord(TopicOptionMapper::class, $this->input_int('topic_option_id'), ['topic']);
            return $evaluation->topic_option->topic->school_id != $topic_option->topic->school_id;
        }

        $school_ids = [];

        if ($council_id = $this->input_int('council_id')) {
            $council = $atlas->fetchRecord(CouncilMapper::class, $council_id, ['council_topics', 'council_grades']);
            $school_ids[] = $council->school_id;
        }
        if ($grade_id = $this->input_int('grade_id')) {
            $grade = $atlas->fetchRecord(GradeMapper::class, $grade_id, ['grade_subjects']);
            $school_ids[] = $grade->school_id;
        }
        if ($student_id = $this->input_int('student_id')) {
            $student = $atlas->fetchRecord(StudentMapper::class, $student_id, ['student_grades']);
            $school_ids[] = $student->school_id;
            $current_student_grade = array_filter($student->student_grades->getArrayCopy(), function($student_grade) {
                return $student_grade['end_date'] >= date(self::DATE_FORMAT);
            })[0] ?? null;
        }
        if ($subject_id = $this->input_int('subject_id')) {
            $subject = $atlas->fetchRecord(SubjectMapper::class, $subject_id);
            $school_ids[] = $subject->school_id;
        }
        if ($topic_option_id = $this->input_int('topic_option_id')) {
            $topic_option = $atlas->fetchRecord(TopicOptionMapper::class, $topic_option_id, ['topic']);
            $school_ids[] = $topic_option->topic->school_id;
        }

        if (count(array_unique($school_ids)) > 1) {
            return true;
        }

        if (!empty($current_student_grade) && $current_student_grade['grade_id'] != $grade_id) {
            return true;
        }

        if (!$council_id || !$grade_id || !$student_id || !$subject_id || !$topic_option_id) {
            return false;
        }

        $council_has_topic = array_filter($council->council_topics->getArrayCopy(), function($council_topic) use ($topic_option) {
            return $council_topic->topic_id == $topic_option->topic_id;
        });
        if (!$council_has_topic) {
            return true;
        }

        $grade_has_subject = array_filter($grade->grade_subjects->getArrayCopy(), function($grade_subject) use ($subject) {
            return $grade_subject['subject_id'] = $subject->id;
        });
        if (!$grade_has_subject) {
            return true;
        }

        $council_has_grade = array_filter($council->council_grades->getArrayCopy(), function($council_grade) use ($grade) {
            return $council_grade['grade_id'] == $grade->id;
        });
        return !$council_has_grade;
    }

    private function check_permission(int $id = null) : bool {
        $atlas = $this->atlas();

        if ($id) {
            $school_id = $atlas->fetchRecord($this->mapper_class_name, $id, ['council'])->council->school_id;
        } else {
            $school_id = $atlas->fetchRecord(CouncilMapper::class, $this->input_int('council_id'))->school_id;
        }

        return $this->has_permission('evaluate', $school_id);
    }

    // METHODS

    public function get() : ?string {
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

        if ($this->has_conflict($id)) {
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