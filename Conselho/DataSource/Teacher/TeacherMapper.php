<?php
namespace Conselho\DataSource\Teacher;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class TeacherMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('grade', GradeMapper::CLASS)->on(['grade_id' => 'id']);
        $this->manyToOne('subject', SubjectMapper::CLASS)->on(['subject_id' => 'id']);
        $this->manyToOne('user', UserMapper::CLASS)->on(['user_id' => 'id']);
    }
}
