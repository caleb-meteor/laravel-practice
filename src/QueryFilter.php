<?php

namespace Caleb\Practice;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class QueryFilter
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected Request $request;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected Builder $query;

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * QueryFilter constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Builder $query
     * @return Builder
     * @author Caleb 2024/11/22
     */
    public function apply(Builder $query)
    {
        $this->query = $query;

        foreach ($this->getFilters() as $name => $value) {
            $value = is_array($value) ? $value : trim($value);
            // 如果值为空，则不执行
            if (!$this->isFilterValue($value)) {
                continue;
            }

            // 如果能找到滤器方法，则调用滤器方法
            if ($method = $this->guessFilterMethod($name)) {
                call_user_func_array([$this, $method], [$value]);
            }
        }

        return $this->query;
    }

    /**
     * @return array
     * @author Caleb 2024/11/22
     */
    public function getFilters()
    {
        return $this->request->all();
    }

    /**
     * @param string $name
     * @return string|null
     * @author Caleb 2024/11/22
     */
    private function guessFilterMethod(string $name)
    {
        // $name=title 方法名称为title
        // $name=category_id 可能的方法名称为 category_id，categoryId
        foreach (array_unique([$name, Str::camel($name)]) as $method) {
            if (method_exists($this, $method) && !method_exists(self::class, $method)) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @param $value
     * @return bool
     * @author Caleb 2024/11/22
     */
    private function isFilterValue($value)
    {
        return $value !== '' && $value !== null && !(is_array($value) && empty($value));
    }

    /**
     * @param $with
     * @return Builder
     * @author Caleb 2024/12/6
     */
    protected function with($with)
    {
        return $this->query->with($with);
    }
}

