<?php

namespace Kakaprodo\CustomData\Lib\TypeHub\Base;

use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Exceptions\UnExpectedArrayItemType;
use Kakaprodo\CustomData\Lib\TypeHub\Base\Traits\HasDataTypeHubHelper;


abstract class DataTypeHubAbstract
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

    /**
     * Laravel validation rules
     */
    public $rules = [];

    /**
     * The error message of a given field
     */
    public $errorMessage = null;

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

            $this->customData->throwError($errorMsg, UnExpectedArrayItemType::class);
        });
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
     * Set laravel request validation rules
     */
    public function rules(array $rules)
    {
        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    /**
     * add a single laravel request validation rule
     */
    public function addRule($ruleName)
    {
        $this->rules[] = $ruleName;

        return $this;
    }

    /**
     * Get laravel rules that can be applied in the FormRequest
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
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
     * Set the error message on a given field
     */
    public function message($message)
    {
        $this->errorMessage = $message;

        return $this;
    }
}
