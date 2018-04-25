<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class School extends Model
{
    static $collection = 'school';

    protected static $attrs = [
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}