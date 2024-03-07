<?php

namespace Kakaprodo\CustomData\Lib\Property;

use Illuminate\Support\Str;
use Kakaprodo\CustomData\CustomData;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * Transform the property name
     * 
     * @param string|closure $newPropertyName
     */
    public function transform($newPropertyName)
    {
        $this->addAfterAuditAction(function () use ($newPropertyName) {
            $this->customData->transformProperties[$this->propertyName] = $this->customData->callFunction(
                $newPropertyName,
                null,
                $this
            );
        });

        return $this;
    }

    /**
     * transform property value to a new value
     * 
     * Note: this will happen after all the property auditing
     * @param string|closure $newValue
     */
    public function castTo($newValue)
    {
        $this->addAfterAuditAction(function () use ($newValue) {

            $propertyName = $this->propertyName;

            $this->copyPropertyValue('original_' . $propertyName);

            $this->customData->$propertyName = CustomData::isCallable($newValue)
                ? $newValue($this->value())
                : $newValue;
        });

        return $this;
    }

    /**
     * transform property value to a laravel Model instance
     * 
     * @param string $fullyClassName : the fully qualified class name of the model
     * @param string? $column : a column to use for retrieving the 
     */
    public function castToModel(string $fullyClassName, string $column = 'id')
    {
        return $this->castTo(
            fn () => $fullyClassName::where($column, $this->value())->first()
        );
    }

    /**
     * Make a copy of the current property value
     * and add it among inputed data
     */
    public function copy($copyName = null, $shouldReplaceProperty = false)
    {
        $this->addAfterAuditAction(fn () => $this->copyPropertyValue($copyName, $shouldReplaceProperty));

        return $this;
    }

    /**
     * Logic to copy a property value
     */
    private function copyPropertyValue($copyName = null, $shouldReplaceProperty = false)
    {
        $copyName = $copyName ?? $this->propertyName . '_copy';

        $copyName = $this->customData->propertyExists($copyName) && !$shouldReplaceProperty
            ? $copyName . '_copy'
            : $copyName;

        $this->customData->$copyName = $this->value();
    }
}
