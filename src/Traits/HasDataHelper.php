<?php

namespace Kakaprodo\CustomData\Traits;

use Illuminate\Support\Arr;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;

/**
 * Where to define the way to access to data 
 */
trait HasDataHelper
{

    /**
     * All data passed to the class
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Grab only some properties form the customData
     */
    public function only($keys = [])
    {
        return Arr::only($this->all(), $keys);
    }

    /**
     * get a given property with the ability to pass
     * a default in case the property is not defined
     */
    public function get($property, $default = null)
    {
        $value = $this->{$property};

        if (isset($value) || $default === null) return $value;

        $this->{$property} = $default;

        return $default;
    }

    /**
     * Convert a given custom data to its original
     * representation(array)
     */
    public function unserialize($data, $type = 'all'): array
    {
        $payload = [];

        foreach ($data as $propertyName => $value) {
            $method = $type == 'all' ? 'unserializeAll' : 'unserializeValidated';
            $payload[$propertyName] = $value instanceof CustomData ? $value->$method() : $value;
        }

        return $payload;
    }

    /**
     * convert all property payload to array
     */
    public function unserializeAll(): array
    {
        return $this->unserialize($this->all(), 'all');
    }

    /**
     * convert validated property payload to array
     */
    public function unserializeValidated(): array
    {
        return $this->unserialize($this->onlyValidated(), 'validated');
    }

    /**
     * Grab a default value of a given property if
     * the property is empty otherwise grab its original
     * value
     */
    public function defaultValue($propertyName)
    {
        $providedValue = $this->$propertyName;

        if (!empty($providedValue)) return  $providedValue;

        $propertyValue =  $this->expectedProperties()[$propertyName] ?? null;

        if (!($propertyValue instanceof DataTypeHub)) return null;

        return $propertyValue->default;
    }

    /**
     * check if a given property exists
     */
    public function propertyExists(string $propertyName)
    {
        return (bool) ($this->data[$propertyName] ?? null);
    }

    /**
     * Get original value of a casted property
     */
    public function originalValue(string $propertyName)
    {
        $originalValue = 'original_' . $propertyName;

        return $this->$originalValue;
    }

    /**
     * All validated properties
     */
    public function onlyValidated(): array
    {
        return Arr::only($this->all(), $this->validatedProperties);
    }

    /**
     * Get all the payload except some properties
     */
    public function except(array $keys = []): array
    {
        return Arr::except($this->all(), $keys);
    }
}
