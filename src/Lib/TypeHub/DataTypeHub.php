<?php

namespace Kakaprodo\CustomData\Lib\TypeHub;

use Exception;
use ReflectionClass;
use Kakaprodo\CustomData\Lib\CustomDataBase;

class DataTypeHub
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

    /**
     * The type to check iin case the provided property type
     * fails
     */
    protected $additionalType = null;

    /**
     * @var CustomDataBase
     */
    protected $customData;

    public $default = null;

    public function __construct(CustomDataBase $customData, $type = null)
    {
        $this->customData = $customData;
        $this->selectedType = $type;
    }

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
     */
    private function isCustomType($selectedType = null)
    {
        return !in_array(
            $selectedType ?? $this->selectedType,
            static::$supportedbuiltInTypes
        );
    }

    /**
     * validate the customData's property type
     */
    public function validate($propertyName)
    {
        if (!$this->selectedType)  throw new Exception(
            "No data type was defined for the property {$propertyName} "
        );

        $propertyValue = $this->customData->get($propertyName, $this->default);
        $selectedType = $this->selectedType;

        if (is_callable($selectedType)) return $selectedType($propertyValue);

        $typeMatches = $this->checkTypeMatches($propertyValue, $selectedType);

        if (!$typeMatches) throw new Exception(
            "Property {$propertyName}: Expected {$this->selectedType} but " .
                gettype($propertyValue) . " given"
        );
    }

    /**
     * Check if the type of a given property matches with 
     * the expected one
     */
    protected function checkTypeMatches(
        $propertyValue,
        $selectedType,
        $checkedExtraType = false
    ) {
        if ($selectedType == null) return false;

        $isCustomType = $this->isCustomType($selectedType);

        $typeMatches = !$isCustomType ?
            $this->checkBuiltInDataType($propertyValue, $selectedType)
            : ($propertyValue instanceof $selectedType);

        return $typeMatches
            ? $typeMatches
            : (($checkedExtraType)
                ?  $typeMatches
                :  $this->checkTypeMatches($propertyValue, $this->additionalType, true));
    }

    private function checkBuiltInDataType($propertyValue, $selectedType)
    {
        $builtInType = "is_" . $selectedType; //eg: is_bool

        return $builtInType($propertyValue);
    }
}
