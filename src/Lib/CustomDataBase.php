<?php

namespace Kakaprodo\CustomData\Lib;

use Kakaprodo\CustomData\Helpers\Optional;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;
use Kakaprodo\CustomData\Traits\HasPropertyHelper;
use Kakaprodo\CustomData\Lib\Property\DataProperty;
use Kakaprodo\CustomData\Traits\HasCustomDataHelper;
use Kakaprodo\CustomData\Exceptions\MissedRequiredPropertyException;

abstract class CustomDataBase
{
    use
        HasCustomDataHelper,
        HasPropertyHelper;

    /**
     * The properties that have been validated
     */
    protected $validatedProperties = [];

    /**
     * Mapping array of properties name transformation
     */
    public $transformProperties = [];

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

    /**
     * Gate to property manupilation
     */
    public function property($customerType = null): DataProperty
    {
        return new DataProperty($this, $customerType);
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
        if (!$this->shouldAuditProperties()) return;

        /**
         * @var DataTypeHub $propertyValue
         */
        foreach ($this->expectedProperties() as $propertyName => $propertyValue) {


            // get the property the way it is with a ? at the end
            $unSinitizePropertyName = is_numeric($propertyName) ? $propertyValue : $propertyName;

            // remove the ? symbol from the name
            $propertyName = $this->replaceLast('?', '', $unSinitizePropertyName);

            $canAudit = ($propertyValue instanceof DataTypeHub);

            $valueForRequiredValidation = $this->get(
                $propertyName,
                $canAudit ? $propertyValue->default : null
            );

            // if a property is optional
            if ($this->strEndsWith($unSinitizePropertyName, '?')) {
                if ($canAudit) $propertyValue->audit($propertyName);

                $this->validatedProperties[$propertyName] = $this->$propertyName;

                continue;
            }

            if ($valueForRequiredValidation === null) $this->throwError(
                $this->optional($propertyValue)->errorMessage ?? "The property {$propertyName} is required on the class " . static::class,
                MissedRequiredPropertyException::class
            );

            // validate the property type
            if ($canAudit) $propertyValue->audit($propertyName);

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

    /**
     * Define whether the package should validate
     * class properties
     */
    public function shouldValidateProperties(): bool
    {
        return true;
    }

    /**
     * Define whether the package should audit(process)
     * class properties.
     */
    public function shouldAuditProperties(): bool
    {
        return $this->shouldValidateProperties();
    }
}
