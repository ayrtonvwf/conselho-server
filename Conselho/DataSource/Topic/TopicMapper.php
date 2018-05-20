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
        $this->manyToOne('school', SchoolMapper::CLASS);
        $this->manyToOne('topic_option', TopicOptionMapper::CLASS);
        $this->oneToMany('council_topics', CouncilTopicMapper::CLASS);
        $this->oneToMany('topic_options', TopicOptionMapper::CLASS);
    }
}
