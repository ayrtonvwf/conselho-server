<?php
namespace Conselho\DataSource\CouncilTopic;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\Council\CouncilMapper;
use Conselho\DataSource\Topic\TopicMapper;

/**
 * @inheritdoc
 */
class CouncilTopicMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('council', CouncilMapper::CLASS)->on(['council_id' => 'id']);
        $this->manyToOne('topic', TopicMapper::CLASS)->on(['topic_id' => 'id']);
    }
}
