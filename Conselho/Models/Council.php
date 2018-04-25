<?php
namespace Conselho\Models;
use Purekid\Mongodm\Model;
use DateTime;

class Council extends Model
{
    static $collection = 'council';

    protected static $attrs = [
        'start_date' => ['type' => Model::DATA_TYPE_DATE],
        'end_date' => ['type' => Model::DATA_TYPE_DATE],
        'name' => ['type' => Model::DATA_TYPE_STRING],
        'school_id' => ['type' => Model::DATA_TYPE_REFERENCE, 'model' => 'Conselho\Models\School'],
        'updated_at' => ['type' => Model::DATA_TYPE_DATE]
    ];

    protected function __preSave() {
        $this->updated_at = new DateTime();
    }
}