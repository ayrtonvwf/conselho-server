<?php
namespace Conselho\Controllers;
use Conselho\Controller;

class RoleType extends Controller
{
    public function __construct() {
        parent::__construct('role_type');
    }

    public function get() {
        $collection = $this->get_collection();
        $filters = $this->get_filters();
        $results = $collection->find($filters)->toArray();
        return json_encode(['results' => $results], $this->prettify());
    }

    private function get_filters() : array {
        $filters = [
            'updated_at' => []
        ];
        if ($this->input('search')) {
            $filters['$text'] = [
                'search' => $this->input('search'),
                'language' => 'pt'
            ];
        }
        if ($this->input('min_updated_at')) {
            $filters['updated_at']['gte'] = new UTCDateTime($this->input('min_updated_at'));
        }
        if ($this->input('max_updated_at')) {
            $filters['updated_at']['lte'] = new UTCDateTime($this->input('max_updated_at'));
        }
        return array_filter($filters);
    }

}