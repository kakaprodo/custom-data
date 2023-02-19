<?php

namespace Kakaprodo\CustomData\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Exceptions\UnCallableValueException;
use Kakaprodo\CustomData\Exceptions\MissedRequiredPropertyException;

trait HasCustomDataHelper
{
    /**
     * Check if a string ends with a given string
     */
    public function strEndsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (
                $needle !== '' && $needle !== null
                && substr($haystack, -strlen($needle)) === (string) $needle
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceLast($search, $replace, $subject)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Throw exception when a field does not exist on customdata
     */
    public function throwWhenFieldAbsent($fieldName, $msg = null)
    {
        if ($this->$fieldName !== null) return;

        throw new MissedRequiredPropertyException($msg ?? "The {$fieldName} field is required");
    }

    /**
     * generatea data unique name that identifiers the customData based on its 
     * property values
     * 
     * @return string
     */
    public function dataKey()
    {
        $keyString = [];

        foreach ($this->expectedProperties() as $key => $value) {
            $property = str_replace('?', '', is_numeric($key) ? $value : $key);

            try {
                $propertValue = $this->serializeValueForKey(
                    $this->get($property, $this->optional($value)->default)
                );
            } catch (\Throwable $th) {
                throw new Exception("was not able to use {$property} for the dataKey");
            }

            if (in_array($property, $this->ignoreForKeyGenerator())) continue;

            $keyString[] = $property . '__eqto__' . $propertValue;
        }

        return implode('-join-', $keyString);
    }

    /**
     * format a given value for data key generating
     */
    protected function serializeValueForKey($value)
    {
        if ($value instanceof Model) return $value->id;

        if (is_array($value)) return implode('-n-', $value);

        if (is_bool($value)) return (int) $value;

        if ($value instanceof CustomData) return $value->dataKey();

        return (string) $value;
    }

    /**
     * only call a function if it is callbale otherwise return error 
     * or return the same passed value
     */
    function callFunction($myFunction, $throwableMsg = null, ...$args)
    {
        if (is_callable($myFunction)) return $myFunction(...$args);

        if ($throwableMsg) throw new UnCallableValueException($throwableMsg);

        return $myFunction;
    }
}
