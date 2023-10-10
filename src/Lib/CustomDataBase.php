<?php

namespace Kakaprodo\CustomData\Lib;

use Exception;
use Kakaprodo\CustomData\Helpers\Optional;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;
use Kakaprodo\CustomData\Traits\HasCustomDataHelper;
use Kakaprodo\CustomData\Exceptions\MissedRequiredPropertyException;

abstract class CustomDataBase
{
    use HasCustomDataHelper;

    /**
     * The properties that have been validated
     */
    protected $validatedProperties = [];

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

            // get the property the way it is with a ? at the end
            $unSinitizePropertyName = is_numeric($propertyName) ? $propertyValue : $propertyName;

            // remove the ? symbol from the name
            $propertyName = $this->replaceLast('?', '', $unSinitizePropertyName);

            $shouldCheckDataType = ($propertyValue instanceof DataTypeHub);

            $valueForRequiredValidation = $this->get(
                $propertyName,
                $shouldCheckDataType ? $propertyValue->default : null
            );

            // if a property is optional
            if ($this->strEndsWith($unSinitizePropertyName, '?')) {

                if ($shouldCheckDataType && $valueForRequiredValidation) {
                    $propertyValue->validate($propertyName);
                }

                $this->validatedProperties[$propertyName] = $this->$propertyName;

                continue;
            }

            if ($valueForRequiredValidation === null) $this->throwError(
                "The property {$propertyName} is required on the class " . static::class,
                MissedRequiredPropertyException::class
            );

            // validate the property type
            if ($shouldCheckDataType) $propertyValue->validate($propertyName);

            $this->validatedProperties[$propertyName] = $this->$propertyName;
        }
    }

    /**
     * Properties to ignore when generating the
     * the data unique key
     */
    protected function ignoreForKeyGenerator(): array
    {
        return [];
    }
}
