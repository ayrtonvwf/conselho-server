<?php
namespace Conselho\DataSource\TeacherRequest;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class TeacherRequestMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('grade', GradeMapper::CLASS);
        $this->oneToMany('subject', SubjectMapper::CLASS);
        $this->oneToMany('user', UserMapper::CLASS);
    }
}
