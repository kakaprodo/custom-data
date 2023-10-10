<?php

namespace Kakaprodo\CustomData\Traits;

use Exception;
use Illuminate\Support\Str;
use Kakaprodo\CustomData\CustomData;
use Illuminate\Database\Eloquent\Model;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;
use Kakaprodo\CustomData\Exceptions\UnCallableValueException;
use Kakaprodo\CustomData\Exceptions\MissedRequiredPropertyException;

trait HasCustomDataHelper
{
    /**
     * the unique key to be geberated only for the validated data
     */
    protected $uniqueCustomDataKey = null;

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
     * Throw exception when none  of the provided fields exist on customdata
     * 
     * @param array|string $fieldName: eg: can be [field1,field2], field1 , field1|field2
     */
    public function throwWhenFieldAbsent($fieldName, $msg = null)
    {
        $fields = is_array($fieldName) ? $fieldName :  explode('|', $fieldName);
        $incrementForAbsence = [];

        foreach ($fields as $field) {
            $field = [
                'name' => $field,
                'exist' => $this->$field ? 1 : 0
            ];

            $incrementForAbsence[] =  $field;
        }

        if (collect($incrementForAbsence)->sum('exist') > 0) return;

        $fieldName = implode(' or ', $fields);

        $this->throwError(
            $msg ?? "The {$fieldName} field is required",
            MissedRequiredPropertyException::class
        );
    }

    /**
     * generatea data unique name that identifiers the customData based on its 
     * property values
     * 
     * @return string
     */
    public function dataKey()
    {
        if ($this->uniqueCustomDataKey) return $this->uniqueCustomDataKey;

        $keyString = [];

        foreach ($this->validatedProperties as $property => $value) {

            if (in_array($property, $this->ignoreForKeyGenerator())) continue;

            try {
                $propertValue = $this->serializeValueForKey($value);
            } catch (\Throwable $th) {
                $this->throwError(
                    "was not able to use {$property} for the dataKey generator, please add the property {$property} among the ignoreForKeyGenerator",
                    Exception::class
                );
            }

            $keyString[] = $property . '__eq__' . $propertValue;
        }

        return $this->uniqueCustomDataKey = Str::slug(implode('-cd-', $keyString));
    }

    /**
     * format a given value for data key generating
     */
    protected function serializeValueForKey($value)
    {
        if ($value instanceof Model) return $value->id;

        if (is_array($value)) return '_array_' . $this->arrayToKey($value);

        if (is_bool($value)) return (int) $value;

        if ($value instanceof CustomData) return $value->dataKey();

        return (string) $value;
    }

    private function arrayToKey($myArray = [])
    {
        $keyStr = [];

        foreach ($myArray as $key => $value) {
            if (is_array($value)) {
                $keyStr[] = $this->arrayToKey($value);
                continue;
            }

            if ($value instanceof CustomData) {
                $keyStr[] = $value->dataKey();
                continue;
            }

            $keyStr[] = $key . '-aj-' . $value;
        }

        return implode('-av-', $keyStr);
    }

    /**
     * only call a function if it is callbale otherwise return error 
     * or return the same passed value
     */
    public function callFunction($myFunction, $throwableMsg = null, ...$args)
    {
        if (is_callable($myFunction)) return $myFunction(...$args);

        if ($throwableMsg)  $this->throwError($throwableMsg, UnCallableValueException::class);

        return $myFunction;
    }

    /**
     * Extract form validation rules from expected data
     */
    public static function formValidationRules()
    {
        $fields = (new static)->expectedProperties();
        $rules = [];

        foreach ($fields as $key => $value) {
            $property = str_replace('?', '', is_numeric($key) ? $value : $key);

            $rule = $value instanceof DataTypeHub ? $value->getRules() : [];

            if ($rule == []) continue;

            $rules[$property] = $rule;
        }

        return $rules;
    }

    /**
     * Gate to throw any exception happening in customData class
     */
    public function throwError($msg, $exceptionClassPath)
    {
        if (method_exists($this, 'customErrorHandling')) {
            try {
                throw new $exceptionClassPath($msg);
            } catch (\Throwable $th) {
                return $this->customErrorHandling($msg, $th);
            }
        }

        throw new $exceptionClassPath($msg);
    }
}
