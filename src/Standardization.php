<?php


namespace Caleb\Practice;

use Illuminate\Database\Eloquent\Builder;

trait Standardization
{
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function toJson($option = JSON_UNESCAPED_UNICODE)
    {
        return parent::toJson($option);
    }

    public function getPerPage()
    {
        return request()->input('per_page', $this->perPage);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function scopeFilter(Builder $query, QueryFilter $filter)
    {
        return $filter->apply($query);
    }
}
