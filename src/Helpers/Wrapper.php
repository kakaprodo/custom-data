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

    /**
     * define type of data to return when 
     * accessing wrappers
     */
    protected $unserializeType = "all"; // or validated

    public function __construct(CustomData &$customData)
    {
        $this->customData = &$customData;
    }

    /**
     * Mention only validated property should be returned
     */
    public function onlyValidated()
    {
        $this->unserializeType = "validated";
        return $this;
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

        return $this->customData->unserialize($properties, $this->unserializeType);
    }

    /**
     * Get all wrappers and maintain their corresponding name
     */
    public function all(): array
    {
        $allWrappers = [];

        foreach ($this->customData->customWrapper as $wrapperName => $properties) {
            $allWrappers[$wrapperName] = $this->get($wrapperName);
        }

        return  $allWrappers;
    }
}
