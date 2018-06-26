<?php
namespace Conselho\DataSource\Topic;

use Atlas\Orm\Mapper\AbstractMapper;
use Conselho\DataSource\CouncilTopic\CouncilTopicMapper;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\DataSource\TopicOption\TopicOptionMapper;

/**
 * @inheritdoc
 */
class TopicMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('school', SchoolMapper::CLASS)->on(['school_id' => 'id']);
        $this->manyToOne('topic_option', TopicOptionMapper::CLASS)->on(['topic_option_id' => 'id']);
        $this->oneToMany('council_topics', CouncilTopicMapper::CLASS)->on(['id' => 'topic_id']);
        $this->oneToMany('topic_options', TopicOptionMapper::CLASS)->on(['id' => 'topic_id']);
    }
}
