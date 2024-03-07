<?php

namespace Kakaprodo\CustomData\Traits;

use Illuminate\Support\Arr;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;

trait HasPropertyHelper
{
    /**
     * Grab only some properties form the customData
     */
    public function only($keys = [])
    {
        return Arr::only($this->all(), $keys);
    }

    /**
     * Convert a given custom data to its original
     * representation(array)
     */
    protected function unserialize($data, $type = 'all'): array
    {
        $payload = [];

        foreach ($data as $propertyName => $value) {
            $method = $type == 'all' ? 'unserializeAll' : 'onlyValidated';
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
        if (!empty($this->$propertyName)) return $this->$propertyName;

        $propertyValue =  $this->expectedProperties()[$propertyName] ?? null;

        if (!($propertyValue instanceof DataTypeHub)) return null;

        return $propertyValue->default;
    }
}
