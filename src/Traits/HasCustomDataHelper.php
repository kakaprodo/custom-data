<?php

namespace Kakaprodo\CustomData\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
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
            $propertValue = $this->serializeValueForKey(
                $this->get($property, $this->optional($value)->default)
            );

            if (in_array($property, $this->ignoreForKeyGenerator())) continue;

            $keyString[] = $property . '=' . $propertValue;
        }

        return implode('@', $keyString);
    }

    /**
     * format a given value for data key generating
     */
    protected function serializeValueForKey($value)
    {
        if ($value instanceof Model) return $value->id;

        if (is_array($value)) return implode(',', $value);

        if (is_bool($value)) return (int) $value;

        return (string) $value;
    }
}
