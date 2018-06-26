<?php
namespace Conselho\DataSource\GradeObservation;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class GradeObservationMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('council', CouncilMapper::CLASS)->on(['council_id' => 'id']);
        $this->manyToOne('grade', GradeMapper::CLASS)->on(['grade_id' => 'id']);
        $this->manyToOne('subject', SubjectMapper::CLASS)->on(['subject_id' => 'id']);
        $this->manyToOne('user', UserMapper::CLASS)->on(['user_id' => 'id']);
    }
}
