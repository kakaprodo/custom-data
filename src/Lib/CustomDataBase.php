<?php

namespace Kakaprodo\CustomData\Lib;

use Kakaprodo\CustomData\Helpers\Optional;
use Kakaprodo\CustomData\Traits\HasDataHelper;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;
use Kakaprodo\CustomData\Lib\Property\DataProperty;
use Kakaprodo\CustomData\Traits\HasCustomDataHelper;
use Kakaprodo\CustomData\Traits\HasDataValidationHelper;
use Kakaprodo\CustomData\Exceptions\MissedRequiredPropertyException;
use Kakaprodo\CustomData\Helpers\Wrapper;

abstract class CustomDataBase
{
    use
        HasCustomDataHelper,
        HasDataHelper,
        HasDataValidationHelper;

    /**
     * kept incoming data and data that will be
     * set at runtime
     */
    protected array $data = [];

    /**
     * The properties that have been validated
     */
    protected $validatedProperties = [];

    /**
     * Mapping array of properties name transformation
     */
    public $transformProperties = [];

    /**
     * Use to group properties
     */
    public $customWrapper = [];

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
     * Property grouping gate
     */
    public function wrapper(): Wrapper
    {
        return new Wrapper($this);
    }

    /**
     * add a given property among the validted ones,if
     * not yet among them
     */
    public function setValidatedProperty($property)
    {
        if (in_array($property, $this->validatedProperties, true)) return $this;

        $this->validatedProperties[] = $property;

        return $this;
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

            $propertyName = $this->replaceLast('?', '', $unSinitizePropertyName);

            $this->throwWhenMagic($propertyName);

            $canAudit = ($propertyValue instanceof DataTypeHub);

            $valueForRequiredValidation = $this->get(
                $propertyName,
                $canAudit ? $propertyValue->default : null
            );

            // if a property is optional
            if ($this->strEndsWith($unSinitizePropertyName, '?')) {
                if ($canAudit) $propertyValue->audit($propertyName);

                $this->setValidatedProperty($propertyName);

                continue;
            }

            if ($valueForRequiredValidation === null) $this->throwError(
                $this->optional($propertyValue)->errorMessage ?? "The property {$propertyName} is required on the class " . static::class,
                MissedRequiredPropertyException::class
            );

            // validate the property type
            if ($canAudit) $propertyValue->audit($propertyName);

            $this->setValidatedProperty($propertyName);
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
