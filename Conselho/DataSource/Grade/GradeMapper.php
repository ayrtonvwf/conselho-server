<?php
namespace Conselho\DataSource\Grade;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\CouncilGrade\CouncilGradeMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\GradeObservation\GradeObservationMapper;
use Conselho\DataSource\GradeSubject\GradeSubjectMapper;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\StudentGrade\StudentGradeMapper;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;
use Conselho\DataSource\Teacher\TeacherMapper;
use Conselho\DataSource\TeacherRequest\TeacherRequestMapper;

/**
 * @inheritdoc
 */
class GradeMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', SchoolMapper::CLASS)->on(['school_id' => 'id']);
        $this->oneToMany('grade_subjects', GradeSubjectMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('grade_observations', GradeObservationMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('student_grades', StudentGradeMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('teachers', TeacherMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('teacher_requests', TeacherRequestMapper::CLASS)->on(['id' => 'grade_id']);
        $this->oneToMany('council_grades', CouncilGradeMapper::CLASS)->on(['id' => 'grade_id']);
    }
}
