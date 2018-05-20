<?php
namespace Conselho\DataSource\StudentGrade;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Student\StudentMapper;

/**
 * @inheritdoc
 */
class StudentGradeMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('grade', GradeMapper::CLASS);
        $this->manyToOne('student', StudentMapper::CLASS);
    }
}
