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
        $this->manyToOne('topic', TopicMapper::CLASS)->on(['topic_id' => 'id']);
        $this->oneToMany('topics', TopicMapper::CLASS)->on(['id' => 'topic_option_id']);
        $this->oneToMany('evaluations', EvaluationMapper::CLASS)->on(['id' => 'topic_option_id']);
    }
}
