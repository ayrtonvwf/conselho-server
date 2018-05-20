<?php
namespace Conselho\DataSource\StudentObservation;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Student\StudentMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class StudentObservationMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('council', CouncilMapper::CLASS);
        $this->manyToOne('grade', GradeMapper::CLASS);
        $this->manyToOne('student', StudentMapper::CLASS);
        $this->manyToOne('subject', SubjectMapper::CLASS);
        $this->manyToOne('user', UserMapper::CLASS);
    }
}
