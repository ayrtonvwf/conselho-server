<?php
namespace Conselho\DataSource\Evaluation;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Grade\GradeMapper;
use Conselho\DataSource\Student\StudentMapper;
use Conselho\DataSource\Subject\SubjectMapper;
use Conselho\DataSource\TopicOption\TopicOptionMapper;
use Conselho\DataSource\User\UserMapper;

/**
 * @inheritdoc
 */
class EvaluationMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('council', CouncilMapper::CLASS)->on(['council_id' => 'id']);
        $this->manyToOne('grade', GradeMapper::CLASS)->on(['grade_id' => 'id']);
        $this->manyToOne('student', StudentMapper::CLASS)->on(['student_id' => 'id']);
        $this->manyToOne('subject', SubjectMapper::CLASS)->on(['subject_id' => 'id']);
        $this->manyToOne('topic_option', TopicOptionMapper::CLASS)->on(['topic_option_id' => 'id']);
        $this->manyToOne('user', UserMapper::CLASS)->on(['user_id' => 'id']);
    }
}
