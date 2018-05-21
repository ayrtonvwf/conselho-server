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
        $this->manyToOne('grade', GradeMapper::CLASS)->on(['grade_id' => 'id']);
        $this->manyToOne('student', StudentMapper::CLASS)->on(['student_id' => 'id']);
    }
}
