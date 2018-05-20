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
        $this->manyToOne('councils', CouncilMapper::CLASS);
        $this->manyToOne('topics', TopicMapper::CLASS);
    }
}
