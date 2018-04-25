<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class Grade extends Model
{
    static $collection = 'grade';

    protected static $attrs = [
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'level' => ['type' => Model::DATA_TYPE_INTEGER],
        'school_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\School'],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}