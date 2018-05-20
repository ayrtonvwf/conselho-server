<?php
namespace Conselho\DataSource\Subject;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\GradeObservation\GradeObservationMapper;
use Conselho\DataSource\GradeSubject\GradeSubjectMapper;
use Conselho\DataSource\MedicalReport\MedicalReportMapper;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;
use Conselho\DataSource\Teacher\TeacherMapper;
use Conselho\DataSource\TeacherRequest\TeacherRequestMapper;

/**
 * @inheritdoc
 */
class SubjectMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', SchoolMapper::CLASS);
        $this->oneToMany('grade_subjects', GradeSubjectMapper::CLASS);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS);
        $this->oneToMany('grade_observations', GradeObservationMapper::CLASS);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS);
        $this->oneToMany('medical_report_subjects', MedicalReportMapper::CLASS);
        $this->oneToMany('teachers', TeacherMapper::CLASS);
        $this->oneToMany('teacher_requests', TeacherRequestMapper::CLASS);
    }
}
