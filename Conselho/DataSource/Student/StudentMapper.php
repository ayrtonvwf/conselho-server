<?php
namespace Conselho\DataSource\Student;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\MedicalReport\MedicalReportMapper;
use Conselho\DataSource\StudentGrade\StudentGradeMapper;
use Conselho\DataSource\StudentObservation\StudentObservationMapper;

/**
 * @inheritdoc
 */
class StudentMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', StudentMapper::CLASS);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS);
        $this->oneToMany('student_observations', StudentObservationMapper::CLASS);
        $this->oneToMany('student_grades', StudentGradeMapper::CLASS);
        $this->oneToMany('medical_reports', MedicalReportMapper::CLASS);
    }
}
