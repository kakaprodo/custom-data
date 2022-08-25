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

    protected $selectedType = null;

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
     * define that a property is a string
     */
    public function string($default = null)
    {
        $this->selectedType = self::DATA_STRING;

        return $this->default($default);
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
     * check if a selected type is a custoomer data Type
     */
    private function isCustomType()
    {
        $builtInTypes = (new ReflectionClass($this))->getConstants();

        return !in_array($this->selectedType, $builtInTypes);
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

        $isCustomType = $this->isCustomType();
        $selectedType = $this->selectedType;

        $typeMatches = !$isCustomType ?
            $this->checkBuiltInDataType($propertyValue)
            : ($propertyValue instanceof $selectedType);

        if (!$typeMatches) throw new Exception(
            "Property {$propertyName}: Expected {$this->selectedType} but " .
                gettype($propertyValue) . " given"
        );
    }

    private function checkBuiltInDataType($propertyValue)
    {
        $builtInType = "is_" . $this->selectedType; //eg: is_bool

        return $builtInType($propertyValue);
    }
}
