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
        $this->manyToOne('council', CouncilMapper::CLASS);
        $this->manyToOne('grade', GradeMapper::CLASS);
        $this->manyToOne('student', StudentMapper::CLASS);
        $this->manyToOne('subject', SubjectMapper::CLASS);
        $this->manyToOne('topic_option', TopicOptionMapper::CLASS);
        $this->manyToOne('user', UserMapper::CLASS);
    }
}
