<?php

namespace Elegant\DataTables\Support;

use Illuminate\Contracts\Support\Arrayable;

class Helper
{
    /**
     * Determines if data is callable or blade string or blade view, processes and returns.
     *
     * @param mixed $data
     * @param array $param
     * @param bool $escape
     * @return mixed
     */
    public static function resolveData($data, array $params = [], $escape = true)
    {
        if (is_callable($data)) {
            $data = call_user_func_array($data, $params);
        }
        // No need to escape blade data, so just return the content
        else {
            return static::resolveBladeData($data, $params);
        }

        if ($escape) {
            return e($data);
        } else {
            return $data;
        }
    }

    /**
     * Determines if data is blade string or blade view, processes and returns.
     *
     * @param mixed $data
     * @param array $param
     * @return mixed
     */
    public static function resolveBladeData($data, array $params = [])
    {
        if (view()->exists($data)) return (string) view($data, $params);

        ob_start() && extract($params, EXTR_SKIP);
        eval('?>'.app('blade.compiler')->compileString($data));
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    /**
     * Casts the value into an array.
     *
     * @param mixed $value
     * @return array
     */
    public static function castToArray($value)
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        } else {
            return (array) $value;
        }
    }

    /**
     * Converts the value into an array.
     *
     * @param mixed $value
     * @return array
     */
    public static function convertToArray($value)
    {
        $values = static::castToArray($value);

        foreach ($values as &$value) {
            if (is_object($value)) {
                $value = static::convertToArray($value);
            }
        }

        return $values;
    }
}
