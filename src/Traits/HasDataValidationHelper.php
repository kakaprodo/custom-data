<?php

namespace Kakaprodo\CustomData\Traits;

use Kakaprodo\CustomData\Exceptions\MagicPropertyDuplicationException;

/**
 * Where we define general validations
 */
trait HasDataValidationHelper
{
    /**
     * check whether a given property name is among 
     * proerties used by the package
     */
    protected function isMagicProperty($propertyName)
    {
        $magicProperties = [
            'data',
            'validatedProperties',
            'transformProperties',
            'customWrapper',
            'uniqueCustomDataKey'
        ];

        return in_array($propertyName, $magicProperties, true);
    }

    /**
     * Prevent the definition of a magic property
     */
    protected function throwWhenMagic($propertyName)
    {
        if (!$this->isMagicProperty($propertyName)) return $this;

        $this->throwError(
            "The property '{$propertyName}' is a magic property and should not be defined,Use another property name",
            MagicPropertyDuplicationException::class
        );
    }
}
