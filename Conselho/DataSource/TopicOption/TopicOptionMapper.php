<?php
namespace Conselho\DataSource\TopicOption;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Evaluation\EvaluationMapper;
use Conselho\DataSource\Topic\TopicMapper;

/**
 * @inheritdoc
 */
class TopicOptionMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('topic', TopicMapper::CLASS);
        $this->oneToMany('topics', TopicMapper::CLASS);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS);
    }
}
