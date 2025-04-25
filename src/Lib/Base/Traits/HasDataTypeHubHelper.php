<?php

namespace Kakaprodo\CustomData\Lib\Base\Traits;

use Exception;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Exceptions\UnExpectedArrayItemType;


/**
 * @property CustomData $customData
 */
trait HasDataTypeHubHelper
{
    /**
     * check if the child of a given array are the instance of the
     * given class
     */
    public function arrayItemsAreCompatible($items, $childType = null)
    {
        if (!$childType) return true;

        if ($childType == self::DATA_ARRAY) $this->customData->throwError(
            $this->errorMessage ?? "child type of {$this->propertyName} is not supported",
            Exception::class
        );

        foreach ($items as $key => $item) {

            $type = $this->isCustomType($childType) ? self::DATA_CUSTOM : $childType;

            $castedItem = $this->custValueToCustomData($item, $childType);

            if ($this->typeOfValueIs($type, $castedItem, $childType)) {
                if (!($item instanceof CustomData)) {
                    $items[$key] = $castedItem;
                }

                continue;
            }

            $this->customData->throwError(
                $this->errorMessage ?? "The item {$this->propertyName}[{$key}] should be of type: " . $childType
                    . " but " . gettype($item) . " given",
                UnExpectedArrayItemType::class
            );
        }

        $this->customData->{$this->propertyName} = $items;

        return true;
    }

    /**
     * check if a given type is custom data class, then cast
     * it to custom data class
     */
    private function custValueToCustomData($value, $type)
    {
        if (($value instanceof CustomData) || !is_array($value)) return $value;

        if (!CustomData::isCustomDataChild($type)) return $value;

        return $type::make(array_merge($this->childProps, $value));
    }


    /**
     * check the type of a given value
     */
    public function typeOfValueIs($type, $value, $customType = null)
    {
        $typeChecker = [
            self::DATA_STRING => fn() => is_string($value) || is_numeric($value),
            self::DATA_INT => fn() => is_integer($value),
            self::DATA_FLOAT => fn() => is_float($value),
            self::DATA_BOOL => fn() => is_bool($value) || in_array(intval($value), [0, 1], true),
            self::DATA_ARRAY => fn() => is_array($value) && $this->arrayItemsAreCompatible($value, $customType),
            self::DATA_OBJECT => fn() => is_object($value),
            self::DATA_NUMERIC => fn() => is_numeric($value),
            self::DATA_CUSTOM => function () use ($value, $customType) {

                if (CustomData::isCallable($customType)) {
                    $result = $customType($value, $this);
                    if ($result) return $result;

                    $this->customData->throwError(
                        $this->errorMessage ?? "Validation failed on {$this->propertyName} property",
                        Exception::class
                    );
                }

                $value = $this->custValueToCustomData($value, $customType);

                $validationPassed =  is_a($value, $customType);

                $this->customData->{$this->propertyName} = $value;

                return $validationPassed;
            }
        ][$type] ?? null;

        return $this->customData->callFunction($typeChecker, 'Unsupported property type :' . $type);
    }
}
