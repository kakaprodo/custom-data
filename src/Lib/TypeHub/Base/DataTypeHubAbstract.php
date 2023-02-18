<?php

namespace Kakaprodo\CustomData\Lib\TypeHub\Base;

use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Exceptions\UnExpectedArrayItemType;


abstract class DataTypeHubAbstract
{
    const DATA_NUMERIC = 'numeric';
    const DATA_BOOL = 'bool';
    const DATA_STRING = 'string';
    const DATA_ARRAY = 'array';

    static $supportedbuiltInTypes = [
        'string',
        'integer',
        'float',
        'bool',
        'array',
        'object',
        'numeric'
    ];

    /**
     * The definedd type of the property 
     */
    protected $selectedType = null;

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
     * set the expected class for each item of an array
     */
    public function itemsShouldBeOfType($class)
    {
        $this->childTypeShouldBe = $class;

        return $this;
    }

    /**
     * check if the child of a given array are the instance of the
     * given class
     */
    public function arrayItemsAreCompatible($items)
    {
        $class = $this->childTypeShouldBe;

        if (!$class) return true;

        foreach ($items as $item) {

            if (is_a($item, $class)) continue;

            throw new UnExpectedArrayItemType(
                "Each child element of {$this->propertyName} should be an instance of: " . $class
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
    public function typeOfValueIs($type, $value)
    {
        $typeChecker = [
            'string' => fn () => is_string($value) || is_numeric($value),
            'integer' => fn () => is_integer($value),
            'float' => fn () => is_float($value),
            'bool' => fn () => is_bool($value) || in_array($value, [0, 1]),
            'array' => fn () => is_array($value) && $this->arrayItemsAreCompatible($value),
            'object' => fn () => is_object($value),
            'numeric' => fn () => is_numeric($value),
        ][$type] ?? null;

        return $this->customData->callFunction($typeChecker, 'Unsupported property type :' . $type);
    }
}
