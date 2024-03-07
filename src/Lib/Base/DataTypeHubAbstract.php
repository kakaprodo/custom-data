<?php

namespace Kakaprodo\CustomData\Lib\Base;

use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Exceptions\MissedRequiredPropertyException;
use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Lib\Base\DataPropertyAbstract;
use Kakaprodo\CustomData\Exceptions\UnExpectedArrayItemType;
use Kakaprodo\CustomData\Lib\Base\Traits\HasDataTypeHubHelper;
use Kakaprodo\CustomData\Exceptions\UnexpectedPropertyTypeException;


abstract class DataTypeHubAbstract extends DataPropertyAbstract
{
    use HasDataTypeHubHelper;

    const DATA_STRING = 'string';
    const DATA_INT = 'integer';
    const DATA_FLOAT = 'float';
    const DATA_BOOL = 'bool';
    const DATA_ARRAY = 'array';
    const DATA_OBJECT = 'object';
    const DATA_NUMERIC = 'numeric';
    const DATA_CUSTOM = 'custom';

    static $supportedbuiltInTypes = [
        self::DATA_STRING,
        self::DATA_INT,
        self::DATA_FLOAT,
        self::DATA_BOOL,
        self::DATA_ARRAY,
        self::DATA_OBJECT,
        self::DATA_NUMERIC
    ];

    /**
     * The type to check iin case the provided property type
     * fails
     */
    protected $additionalType = null;

    /**
     * applicable on array child
     */
    public $childTypeShouldBe = null;

    /**
     * The validation error message of a given field
     */
    public $errorMessage = null;

    /**
     * closure keeper, of the casted value of the property 
     */
    protected $castForValidation = null;

    public function __construct(CustomDataBase &$customData, $type = null)
    {
        $this->customData = &$customData;
        $this->selectedType = $type;
    }

    /**
     * validate a given property of the custom data
     */
    abstract public function validate($propertyName);

    /**
     * define that a property is a number
     */
    public function numeric($default = null)
    {
        $this->selectedType = self::DATA_NUMERIC;

        return $this->default($default);
    }

    /**
     * the numeric clone
     */
    public function number($default = null)
    {
        return $this->numeric($default);
    }

    /**
     * define that a property is a boolean
     */
    public function bool($default = null)
    {
        $this->selectedType = self::DATA_BOOL;

        return $this->default($default);
    }

    /**
     * define that a property is an array
     */
    public function array($default = null)
    {
        $this->selectedType = self::DATA_ARRAY;

        return $this->default($default);
    }

    /**
     * define that a property is a string
     */
    public function string($default = null)
    {
        $this->selectedType = self::DATA_STRING;

        return $this->default($default);
    }

    /**
     * Not tolerate empty value on property
     */
    public function notEmpty()
    {
        $this->addBeforeAuditAction(function () {
            if (!empty($this->value())) return;

            $this->customData->throwError(
                $this->errorMessage ??
                    "The Property {$this->propertyName} of "
                    . get_class($this->customData)
                    . " should not be empty.",
                UnexpectedPropertyTypeException::class
            );
        });

        return $this;
    }


    /**
     * Define your proper way to check the property type,
     * and handle exception on error
     */
    public function customValidator(callable $logic)
    {
        $this->selectedType = $logic;

        return $this;
    }

    /**
     * cast the value of a given property so that it can pass 
     * the validation
     */
    public function castForValidation(callable $castCall)
    {
        $this->castForValidation = $castCall;

        return $this;
    }

    /**
     * Get the value of the property after its casting,
     * if no casting provided then return its original value.
     * 
     * Note this is only for validation, it will not be kept on 
     * the property
     */
    protected function castValue($propertyValue)
    {
        return $this->customData->callFunction(
            $this->castForValidation,
            null,
            $propertyValue
        ) ?? $propertyValue;
    }

    /**
     * set the expected type for each item of an array
     */
    public function isArrayOf($type)
    {
        $this->childTypeShouldBe = $type;

        $this->selectedType = self::DATA_ARRAY;

        return $this;
    }

    /**
     * the value is one of the given item in array
     */
    public function inArray(array $items)
    {
        return $this->customValidator(function ($value) use ($items) {
            if (in_array($value, $items)) return true;

            $errorMsg = "{$this->propertyName} should be one of: " . implode(',', $items);
            $errorMsg .= " but {$value} given";

            $this->customData->throwError($this->errorMessage ?? $errorMsg, UnExpectedArrayItemType::class);
        });
    }

    /**
     * A additional type to check in case the first type failed
     */
    public function orUseType($type)
    {
        $this->additionalType = $type;

        return $this;
    }

    /**
     * check if a selected type is a custoomer data Type
     * and not among the built in type
     */
    public function isCustomType($selectedType = null)
    {
        return !in_array(
            $selectedType ?? $this->selectedType,
            static::$supportedbuiltInTypes
        );
    }

    /**
     * Set the validation error message on a given field
     */
    public function message($message)
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * Define a condition when an optional property need
     * to be required.
     * 
     * @param string|callable $property
     * @param mixed $value
     */
    public function requiredWhen($property, $value = null)
    {
        $this->addBeforeAuditAction(function () use ($property, $value) {
            if (!empty($this->value())) return;

            $isRequired = CustomData::isCallable($property) ?
                $property($this)
                : ($this->customData->defaultValue($property) == $value);

            if (!$isRequired) return;

            $errorMsg = CustomData::isCallable($property) ?
                "The property {$this->propertyName} is required when the property {$property} is equal to :" . $value
                : "The property {$this->propertyName} is required because of the provided statement";

            $this->customData->throwError($this->errorMessage ?? $errorMsg, MissedRequiredPropertyException::class);
        });

        return $this;
    }

    /**
     * Change property name when a given statement is true
     * 
     * @param string|callable $statement
     * @param string|callable $newType : any supported type
     */
    public function typeWhen($statement, $newType)
    {
        $this->addBeforeAuditAction(function () use ($statement, $newType) {
            $canChangeType = $this->customData->callFunction($statement, null, $this);

            if ($canChangeType) $this->selectedType = $this->customData->callFunction(
                $newType,
                null,
                $this
            );
        });

        return $this;
    }
}
