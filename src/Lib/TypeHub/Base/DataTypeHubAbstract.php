<?php

namespace Kakaprodo\CustomData\Lib\TypeHub\Base;

use Exception;
use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Exceptions\UnExpectedArrayItemType;


abstract class DataTypeHubAbstract
{

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
     * The definedd type of the property 
     */
    protected $selectedType = null;

    /**
     * the name of the property we are validating
     */
    protected $propertyName = null;

    /**
     * The type to check iin case the provided property type
     * fails
     */
    protected $additionalType = null;

    /**
     * carry a function that cast a property to a given type
     */
    protected $cast = null;

    /**
     * @var CustomDataBase
     */
    protected $customData;

    public $default = null;

    /**
     * applicable on array child
     */
    public $childTypeShouldBe = null;

    public function __construct(CustomDataBase $customData, $type = null)
    {
        $this->customData = $customData;
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
        $this->cast = $castCall;

        return $this;
    }

    /**
     * get the value of the property after its casting,
     * if no casting provided then return its original value
     */
    public function castValue($propertyValue)
    {
        return $this->customData->callFunction($this->cast, null, $propertyValue) ?? $propertyValue;
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

            throw new UnExpectedArrayItemType($errorMsg);
        });
    }

    /**
     * check if the child of a given array are the instance of the
     * given class
     */
    public function arrayItemsAreCompatible($items, $childType = null)
    {
        if (!$childType) return true;

        if ($childType == self::DATA_ARRAY) throw new Exception(
            "child type of {$this->propertyName} is not supported"
        );

        foreach ($items as $key => $item) {

            $type = $this->isCustomType($childType) ? self::DATA_CUSTOM : $childType;

            if ($this->typeOfValueIs($type, $item, $childType)) continue;

            throw new UnExpectedArrayItemType(
                "The item {$this->propertyName}[{$key}] should be of type: " . $childType
                    . " but " . gettype($item) . " given"
            );
        }

        return true;
    }

    /**
     * Set a default value of a data type
     */
    public function default($default = null)
    {
        $this->default = $default;

        return $this;
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
                    $result = $customType($value);
                    if ($result) return $result;

                    throw new Exception(
                        "Validation failed on {$this->propertyName} property"
                    );
                }

                return is_a($value, $customType);
            }
        ][$type] ?? null;

        return $this->customData->callFunction($typeChecker, 'Unsupported property type :' . $type);
    }
}
