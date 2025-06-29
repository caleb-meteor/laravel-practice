<?php


namespace Caleb\Practice;

use Illuminate\Database\Eloquent\Builder;

trait Standardization
{
    protected function asJson($value, $flags = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($value, $flags);
    }

    public function toJson($option = JSON_UNESCAPED_UNICODE)
    {
        return parent::toJson($option);
    }

    public function getPerPage()
    {
        return request()->input('per_page', $this->perPage);
    }

    public function scopeFilter(Builder $query, QueryFilter $filter)
    {
        return $filter->apply($query);
    }
}
