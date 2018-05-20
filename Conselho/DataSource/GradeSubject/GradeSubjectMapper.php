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
        $this->manyToOne('subjects', SubjectMapper::CLASS);
        $this->manyToOne('grades', GradeMapper::CLASS);
    }
}
