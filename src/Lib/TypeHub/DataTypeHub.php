<?php

namespace Kakaprodo\CustomData\Lib\TypeHub;

use Exception;
use Kakaprodo\CustomData\Lib\TypeHub\Base\DataTypeHubAbstract;
use Kakaprodo\CustomData\Exceptions\UnexpectedPropertyTypeException;

class DataTypeHub extends DataTypeHubAbstract
{
    /**
     * validate the customData's property type
     */
    public function validate($propertyName)
    {
        $this->propertyName = $propertyName;

        if (!$this->selectedType) $this->customData->throwError(
            "No data type was defined for the property {$propertyName} ",
            Exception::class
        );

        $propertyValue = $this->castValue($this->customData->get($propertyName, $this->default));
        // $selectedType = $this->selectedType;

        //if (is_callable($selectedType)) return $selectedType($propertyValue, $this);

        $typeMatches = $this->checkTypeMatches($propertyValue, $this->selectedType);

        if (!$typeMatches) $this->customData->throwError(
            $this->errorMessage ?? "Property {$propertyName} of " . get_class($this->customData) . ": Expected {$this->selectedType} but " .
                gettype($propertyValue) . " given",
            UnexpectedPropertyTypeException::class
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

        $typeMatches = $this->isCustomType($selectedType) ?
            $this->typeOfValueIs(self::DATA_CUSTOM, $propertyValue, $selectedType)
            : $this->typeOfValueIs($selectedType, $propertyValue, $this->childTypeShouldBe);

        return $typeMatches
            ? $typeMatches
            : (($checkedExtraType)
                ?  $typeMatches
                :  $this->checkTypeMatches($propertyValue, $this->additionalType, true));
    }
}
