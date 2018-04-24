<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;

class TopicType extends Controller
{
    public function __construct() {
        parent::__construct('topic_type');
    }

    public function get() {
        $collection = $this->get_collection();
        $results = $collection->find([])->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }
}