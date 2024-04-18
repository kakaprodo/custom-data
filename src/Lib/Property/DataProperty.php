<?php

namespace Kakaprodo\CustomData\Lib\Property;

use Illuminate\Support\Str;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;

class DataProperty extends DataTypeHub
{

    public function __construct(CustomDataBase &$customData, $type = null)
    {
        parent::__construct($customData, $type);

        $this->copyExternalTransformationToInline();
    }

    /**
     * Convert the current property name to camelCase
     */
    public function toCamelCase()
    {
        $this->addBeforeAuditAction(
            fn () => $this->transform(fn () => Str::camel($this->propertyName))
        );

        return $this;
    }

    /**
     * Convert the current property name to kebab-case
     */
    public function toKebabCase()
    {
        $this->addBeforeAuditAction(
            fn () => $this->transform(fn () => Str::kebab($this->propertyName))
        );

        return $this;
    }

    /**
     * Convert the current property name to snake_case
     */
    public function toSnakeCase()
    {
        $this->addBeforeAuditAction(
            fn () => $this->transform(fn () => Str::snake($this->propertyName))
        );

        return $this;
    }

    /**
     * Convert the current property name to PascalCase
     */
    public function toPascalCase()
    {
        $this->addBeforeAuditAction(
            fn () => $this->transform(function () {
                $str = ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $this->propertyName));

                return str_replace(' ', '', $str);
            })
        );

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
            $newName = $this->customData->callFunction(
                $newPropertyName,
                null,
                $this
            );

            $this->customData->propertyNameTransformation([
                $this->propertyName => $newName
            ]);

            $this->propertyName = $newName;
        });

        return $this;
    }

    /**
     * Take the outline transformation defined for the current
     * property and add it to the inline transformation
     */
    private function copyExternalTransformationToInline()
    {
        return $this->addBeforeAuditAction(function () {
            $newPropertyName = $this->customData->transformProperties[$this->propertyName] ?? null;
            if (!$newPropertyName) return;

            $this->transform($newPropertyName);

            unset($this->customData->transformProperties[$this->propertyName]);
        });
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
     * @param string? $column : a column to use for retrieving the model record
     */
    public function castToModel(string $fullyClassName, string $column = 'id')
    {
        return $this->castTo(function () use ($fullyClassName, $column) {
            if (!($value = $this->value())) return;

            return $fullyClassName::where($column, $value)->first();
        });
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

    /**
     * add the current property to a given group
     */
    public function wrap(string $groupName)
    {
        $this->addAfterAuditAction(
            fn () => $this->customData->wrapper()->add($groupName, $this->propertyName),
            self::ACTION_WRAPPER
        );

        return $this;
    }
}
