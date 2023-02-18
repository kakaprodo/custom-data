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

        if (!$this->selectedType)  throw new Exception(
            "No data type was defined for the property {$propertyName} "
        );

        $propertyValue = $this->castValue($this->customData->get($propertyName, $this->default));
        $selectedType = $this->selectedType;

        if (is_callable($selectedType)) return $selectedType($propertyValue);

        $typeMatches = $this->checkTypeMatches($propertyValue, $selectedType);

        if (!$typeMatches) throw new UnexpectedPropertyTypeException(
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

        $typeMatches = $this->isCustomType($selectedType) ?
            ($propertyValue instanceof $selectedType)
            : $this->typeOfValueIs($selectedType, $propertyValue);

        return $typeMatches
            ? $typeMatches
            : (($checkedExtraType)
                ?  $typeMatches
                :  $this->checkTypeMatches($propertyValue, $this->additionalType, true));
    }
}
