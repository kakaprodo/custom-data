<?php

namespace Kakaprodo\CustomData\Helpers;

use Illuminate\Support\Arr;
use Kakaprodo\CustomData\CustomData;

/**
 * Use to manupilate data properties groupong
 */
class Wrapper
{
    /**
     * @var CustomData
     */
    protected $customData;

    public function __construct(CustomData &$customData)
    {
        $this->customData = &$customData;
    }

    /**
     * add property to a group wrapper
     */
    public function add(string $groupName, string $propertyName)
    {
        $this->customData->customWrapper[$groupName][] = $propertyName;

        return $this;
    }

    /**
     * get a given group wrapped
     */
    public function get(string $groupName): array
    {
        $wrappedProperties = $this->customData->customWrapper[$groupName] ?? [];

        $properties =  Arr::only($this->customData->all(), $wrappedProperties);

        return $this->customData->unserialize($properties, 'all');
    }
}
