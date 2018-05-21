<?php
namespace Conselho\DataSource\GradeSubject;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Subject\SubjectMapper;

/**
 * @inheritdoc
 */
class GradeSubjectMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('subject', SubjectMapper::CLASS)->on(['subject_id' => 'id']);
        $this->manyToOne('grade', GradeMapper::CLASS)->on(['grade_id' => 'id']);
    }
}
