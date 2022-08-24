<?php

namespace Kakaprodo\CustomData\Lib;

use Exception;
use Kakaprodo\CustomData\Helpers\Optional;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;
use Kakaprodo\CustomData\Traits\HasCustomDataHelper;

abstract class CustomDataBase
{
    use HasCustomDataHelper;
    /**
     * Required  class properties 
     * 
     * Note: when defining property, use  the suffix `?` to 
     * your property for defining it as optional
     */
    abstract protected function expectedProperties(): array;

    /**
     * define a type of a given property 
     */
    public function dataType($customerType = null): DataTypeHub
    {
        return new DataTypeHub($this, $customerType);
    }

    public function optional($object)
    {
        return new Optional($object);
    }

    /**
     * Validatee properties
     */
    protected function validateRequiredProperties()
    {

        foreach ($this->expectedProperties() as $propertyName => $propertyValue) {

            // in case data type checking is not supported
            $propertyName = is_numeric($propertyName) ? $propertyValue : $propertyName;

            $shouldCheckDataType = ($propertyValue instanceof DataTypeHub);

            // if a property is optional
            if ($this->strEndsWith($propertyName, '?')) {

                if ($shouldCheckDataType && $this->optional($propertyValue)->default) {
                    $propertyValue->validate($propertyName);
                }

                continue;
            }

            $valueForRequiredValidation = $this->get(
                $propertyName,
                $shouldCheckDataType ? $propertyValue->default : null
            );

            if ($valueForRequiredValidation === null) throw new Exception(
                "The property {$propertyName} is required on the class " . static::class
            );

            // validate the property type
            if ($shouldCheckDataType) $propertyValue->validate($propertyName);
        }
    }
}
