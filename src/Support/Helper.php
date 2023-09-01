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
     * @return string
     */
    public static function resolveData($data, array $params = [], $escape = true)
    {
        if (is_callable($data)) {
            // NOTE: $params does include keys and variable names, but for methods, we don't want to use PHP 8 named arguments, so we just use array_values to fix the problem
            $data = call_user_func_array($data, array_values($params));
        }
        else {
            // No need to escape blade data, so just return the content
            return static::resolveBladeData($data, $params);
        }

        if ($escape and is_string($data)) {
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
     * @return string
     */
    public static function resolveBladeData($data, array $params = [])
    {
        if (view()->exists($data)) return (string) view($data, $params);

        ob_start() && extract($params, EXTR_SKIP);
        eval('?>'.app('blade.compiler')->compileString($data));
        $data = ob_get_contents();
        ob_end_clean();

        return $data ?: '';
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
