<?php

namespace Kakaprodo\CustomData\Lib\TypeHub\Base\Traits;

use Exception;
use ReflectionClass;
use Kakaprodo\CustomData\CustomData;


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
            "child type of {$this->propertyName} is not supported",
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
                "The item {$this->propertyName}[{$key}] should be of type: " . $childType
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

        if (!class_exists($type)) return $value;

        $getTopParent = $this->getTopmostParentClassName($type, CustomData::class);

        if ($getTopParent != CustomData::class) return $value;

        return $type::make($value);
    }

    /**
     * Get the latest parent of a given class until to find
     * the provided parentClassToSearch
     */
    private function getTopmostParentClassName($className, $parentClassToSearch = null)
    {
        $reflectionClass = new ReflectionClass($className);
        $stopSearching = false;

        while ((($reflectionParentClass = $reflectionClass->getParentClass()) && !$stopSearching)) {
            $reflectionClass = $reflectionParentClass;

            if ($reflectionClass->getName() == $parentClassToSearch) {
                $stopSearching = true;
            }
        }

        return $reflectionClass->getName();
    }


    /**
     * check the type of a given value
     */
    public function typeOfValueIs($type, $value, $customType = null)
    {
        $typeChecker = [
            self::DATA_STRING => fn () => is_string($value) || is_numeric($value),
            self::DATA_INT => fn () => is_integer($value),
            self::DATA_FLOAT => fn () => is_float($value),
            self::DATA_BOOL => fn () => is_bool($value) || in_array($value, [0, 1]),
            self::DATA_ARRAY => fn () => is_array($value) && $this->arrayItemsAreCompatible($value, $customType),
            self::DATA_OBJECT => fn () => is_object($value),
            self::DATA_NUMERIC => fn () => is_numeric($value),
            self::DATA_CUSTOM => function () use ($value, $customType) {

                if (is_callable($customType)) {
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
