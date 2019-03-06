<?php
namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\ObservationTopic\ObservationTopicMapper;
use Conselho\DataSource\Student\StudentMapper;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\User\UserMapper;

class StudentObservation extends Controller
{
    public function __construct()
    {
        parent::__construct(StudentObservationMapper::class);
    }

    private function get_get_data() : array {
        return array_filter([
            'description LIKE ?' => $this->input_search('search'),
            'council_id = ?' => $this->input_int('council_id'),
            'grade_id = ?' => $this->input_int('grade_id'),
            'student_id = ?' => $this->input_int('student_id'),
            'subject_id = ?' => $this->input_int('subject_id'),
            'user_id = ?' => $this->input_int('user_id'),
            'observation_topic_id = ?' => $this->input_int('observation_topic_id')
        ]);
    }

    private function get_post_data() : array {
        return [
            'description' => $this->input_string('description'),
            'council_id' => $this->input_int('council_id'),
            'grade_id' => $this->input_int('grade_id'),
            'student_id' => $this->input_int('student_id'),
            'subject_id' => $this->input_int('subject_id'),
            'observation_topic_id' => $this->input_int('observation_topic_id'),
            'user_id' => $this->get_user()->id
        ];
    }

    private function get_patch_data() : array {
        return [
            'description' => $this->input_string('description'),
            'updated_at' => date(self::DATETIME_INTERNAL_FORMAT)
        ];
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'search'  => ['optional', ['lengthBetween', 3, 50]],
            'council_id'  => ['optional', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'grade_id'  => ['optional', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'student_id' => ['optional', 'integer', ['min', 1], ['id_exists', StudentMapper::class]],
            'subject_id' => ['optional', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'user_id' => ['optional', 'integer', ['min', 1], ['id_exists', UserMapper::class]],
            'observation_topic_id' => ['optional', 'integer', ['min', 1], ['id_exists', ObservationTopicMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_post() : bool {
        $rules = [
            'description' => ['required', ['lengthBetween', 3, 3000]],
            'council_id' => ['required', 'integer', ['min', 1], ['id_exists', CouncilMapper::class]],
            'grade_id' => ['required', 'integer', ['min', 1], ['id_exists', GradeMapper::class]],
            'student_id' => ['required', 'integer', ['min', 1], ['id_exists', StudentMapper::class]],
            'subject_id' => ['required', 'integer', ['min', 1], ['id_exists', SubjectMapper::class]],
            'observation_topic_id' => ['required', 'integer', ['min', 1], ['id_exists', ObservationTopicMapper::class]]
        ];

        return $this->run_validation($rules);
    }

    private function validate_patch() : bool {
        $rules = [
            'description' => ['required', ['lengthBetween', 3, 3000]]
        ];

        return $this->run_validation($rules);
    }

    private function has_conflict() : bool {
        $atlas = $this->atlas();
        $school_ids = [];

        if ($council_id = $this->input_int('council_id')) {
            $council = $atlas->fetchRecord(CouncilMapper::class, $council_id, ['council_grades']);
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

        if (count(array_unique($school_ids)) > 1) {
            return true;
        }

        if (!$council_id || !$grade_id || !$student_id || !$subject_id) {
            return false;
        }

        if (empty($current_student_grade) || $current_student_grade['grade_id'] != $grade_id) {
            return true;
        }

        $council_has_grade = array_filter($council->council_grades->getArrayCopy(), function($council_grade) use ($grade) {
            return $council_grade['grade_id'] == $grade->id;
        });
        if (!$council_has_grade) {
            return true;
        }

        $grade_has_subject = array_filter($grade->grade_subjects->getArrayCopy(), function($grade_subject) use ($subject) {
            return $grade_subject['subject_id'] = $subject->id;
        });
        return !$grade_has_subject;
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
