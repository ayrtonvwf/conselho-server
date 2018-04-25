<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class User extends Model
{
    static $collection = 'user';

    protected static $attrs = [
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'email' => ['type' => Model::DATA_TYPE_STRING],
        'password' => ['type' => Model::DATA_TYPE_STRING],
        'dev' => ['type' => Model::DATA_TYPE_BOOLEAN],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}