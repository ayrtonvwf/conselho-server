<?php
namespace Conselho;
use MongoDB;

abstract class Controller {
    protected $db;

    public function __construct() {
        $db_client = new MongoDB\Client;
        $db_name = getenv('database');
        $this->db = $db_client->db_name;
    }
}