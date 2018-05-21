<?php
namespace Conselho\DataSource\MedicalReport;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\MedicalReportSubject\MedicalReportSubjectMapper;
use Conselho\DataSource\Student\StudentMapper;

/**
 * @inheritdoc
 */
class MedicalReportMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('student', StudentMapper::CLASS)->on(['student_id' => 'id']);
        $this->oneToMany('medical_report_subjects', MedicalReportSubjectMapper::CLASS)->on(['id' => 'medical_report_id']);
    }
}
