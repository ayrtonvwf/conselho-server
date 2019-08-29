<?php

declare(strict_types=1);

namespace Conselho\Controllers;
use Conselho\Controller;
use Conselho\DataSource\School\SchoolMapper;
use Conselho\Models;

class School extends Controller
{
    public function __construct()
    {
        parent::__construct(SchoolMapper::class);
    }

    // VALIDATION

    private function validate_get() : bool {
        $rules = self::DEFAULT_GET_RULES + [
            'search'  => ['optional', ['lengthBetween', 3, 50]]
        ];

        return $this->run_validation($rules);
    }

    // METHODS

    public function get() : string {
        if (!$this->validate_get()) {
            http_response_code(400);
            return $this->input_error_output();
        }

        $query = Models\School::getQuery();

        if ($this->has_input('search')) {
            $query->where('name', 'like', $this->input_search('search'));
        }
        if ($this->has_input('id')) {
            $query->where('id', $this->input_int('id'));
        }
        if ($this->has_input('min_created_at')) {
            $query->where('created_at', '>=', $this->input_datetime('min_created_at'));
        }
        if ($this->has_input('max_created_at')) {
            $query->where('created_at', '<=', $this->input_datetime('max_created_at'));
        }
        if ($this->has_input('min_updated_at')) {
            $query->where('updated_at', '>=', $this->input_datetime('min_updated_at'));
        }
        if ($this->has_input('max_updated_at')) {
            $query->where('updated_at', '<=', $this->input_datetime('max_updated_at'));
        }

        $result = $this->paginate($query);

        return json_encode($result, $this->pretty());
    }

}