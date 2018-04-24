<?php
namespace Conselho\Controllers;
use MiladRahimi\PHPRouter\Request;
use Conselho\Controller;

class School extends Controller
{
    public function __construct() {
        parent::__construct('school');
    }

    public function get() {
        $collection = $this->get_collection();
        $results = $collection->find([])->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }
}