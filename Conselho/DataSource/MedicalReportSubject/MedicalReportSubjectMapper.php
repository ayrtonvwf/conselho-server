<?php
namespace Conselho\DataSource\MedicalReportSubject;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\MedicalReport\MedicalReportMapper;
use Conselho\DataSource\Subject\SubjectMapper;

/**
 * @inheritdoc
 */
class MedicalReportSubjectMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('medical_report', MedicalReportMapper::CLASS)->on(['medical_report_id' => 'id']);
        $this->manyToOne('subject', SubjectMapper::CLASS)->on(['subject_id' => 'id']);
    }
}
