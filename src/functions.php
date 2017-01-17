<?php

namespace Meare\Juggler;

/**
 * Recursively checks if array1 is sub-array of array2. Both keys and values must match.
 *
 * @param array $a1
 * @param array $a2
 * @return bool
 */
function is_subarray_assoc(array $a1, array $a2)
{
    $result = true;
    foreach ($a1 as $key => $value) {
        if (!isset($a2[$key])) {
            return false;
        } elseif (is_array($value) && is_array($a2[$key])) {
            $result = is_subarray_assoc($value, $a2[$key]);
        } else {
            $result = $value == $a2[$key];
        }
        if (!$result) {
            return false;
        }
    }

    return $result;
}

/**
 * Strips all array elements which value strictly equals to null
 *
 * @param array $array
 * @return array
 */
function array_filter_null(array $array)
{
    return array_filter($array, function ($var) {
        return null !== $var;
    });
}

/**
 * Makes empty array to be serialized as "{}" (empty object) in JSON
 *
 * @param array $array
 * @return array|\stdClass
 */
function json_object(array $array)
{
    return sizeof($array) > 0 ? $array : new \stdClass;
}

/**
 * Wrapper that throws exception
 *
 * @see file_get_contents()
 * @return string|bool
 * @throws \RuntimeException
 */
function file_get_contents()
{
    if (false === $contents = @call_user_func_array('file_get_contents', func_get_args())) {
        throw new \RuntimeException(error_get_last()['message']);
    }

    return $contents;
}

/**
 * Wrapper that throws exception
 *
 * @see file_put_contents()
 * @return int|bool
 * @throws \RuntimeException
 */
function file_put_contents()
{
    if (false === $result = @call_user_func_array('file_put_contents', func_get_args())) {
        throw new \RuntimeException(error_get_last()['message']);
    }

    return $result;
}
