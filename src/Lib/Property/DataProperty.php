<?php

namespace Kakaprodo\CustomData\Lib\Property;

use Illuminate\Support\Str;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;

class DataProperty extends DataTypeHub
{
    /**
     * Converts the current property name to camelCase
     */
    public function toCamelCase()
    {
        $this->addAfterAuditAction(
            fn () =>  $this->customData->transformProperties[$this->propertyName] = Str::camel($this->propertyName)
        );

        return $this;
    }

    /**
     * Converts the current property name to kebab-case
     */
    public function toKebabCase()
    {
        $this->addAfterAuditAction(
            fn () => $this->customData->transformProperties[$this->propertyName] = Str::kebab($this->propertyName)
        );

        return $this;
    }

    /**
     * Converts the current property name to snake_case
     */
    public function toSnakeCase()
    {
        $this->addAfterAuditAction(
            fn () =>  $this->customData->transformProperties[$this->propertyName] = Str::snake($this->propertyName)
        );

        return $this;
    }

    /**
     * Converts the current property name to PascalCase
     */
    public function toPascalCase()
    {
        $this->addAfterAuditAction(function () {
            $str = ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $this->propertyName));

            $str = str_replace(' ', '', $str);

            $this->customData->transformProperties[$this->propertyName] = $str;
        });

        return $this;
    }
}
