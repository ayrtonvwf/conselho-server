<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;

class Student extends Model
{
    static $collection = 'student';

    protected static $attrs = [
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'school_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\School'],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}