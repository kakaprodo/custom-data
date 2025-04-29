<?php

namespace Kakaprodo\CustomData\Lib\Base;

use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Lib\Property\DataProperty;

abstract class DataPropertyAbstract
{
    /**
     * @var CustomDataBase
     */
    protected $customData;

    /**
     * the name of the property we are validating
     */
    protected $propertyName = null;

    /**
     * The definedd type of the property 
     */
    protected $selectedType = null;

    /**
     * applicable on array child
     */
    public $childTypeShouldBe = null;

    /**
     * keeps action to perform to property before the audit
     */
    protected $beforeAuditActions = [];

    /**
     * keeps action to perform to property after the audit
     */
    protected $afterAuditActions = [];

    /**
     * Laravel validation rules
     * 
     * @var callable|array
     */
    public $rules = [];

    /**
     * carry a function that cast a property to a given type
     */
    protected $cast = null;

    /**
     * property default value
     */
    public $default = null;

    /**
     * Properties to pass to a child custom-data class
     */
    public $childProps = [];

    /**
     * The basic nature of a property
     */
    const PROPERTY_NATURE_REQUIRED = "required";
    const PROPERTY_NATURE_OPTIONAL = "optional";

    /**
     * Action Group Name
     */
    const ACTION_GENERAL = 'GENERAL';
    const ACTION_WRAPPER = 'WRAPPER';
    const ACTION_EXTRA = 'EXTRA';

    /**
     * the lifecycle of action execution
     */
    static $eventExecutionOrders = [
        self::ACTION_GENERAL,
        self::ACTION_WRAPPER,
        self::ACTION_EXTRA,
    ];

    /**
     * which defines whether a property is optional or required
     */
    protected $propertyNature = null;

    /**
     * validate a given property
     */
    abstract public function validate($propertyName);

    /**
     * Audit a given single property name of the inputed data
     */
    public function audit($propertyName, $isOptional = false)
    {
        $this->propertyName = $propertyName;
        $this->propertyNature = $isOptional
            ? self::PROPERTY_NATURE_OPTIONAL
            : self::PROPERTY_NATURE_REQUIRED;

        $this->executeBeforeAuditActions();

        if ($this->canValidateProperty()) $this->validate($propertyName);

        $this->executeAfterAuditActions();

        $this->registerToValidation();

        return $this;
    }

    /**
     * check if a property can be validated
     */
    private function canValidateProperty()
    {
        if ($this instanceof DataProperty) {
            return $this->selectedType && $this->value() !== null;
        }

        if ($this->propertyNature == self::PROPERTY_NATURE_OPTIONAL) {
            return $this->value() !== null;
        }

        // for required properties
        return true;
    }

    /**
     * Grab the value of the current property if it exists,
     * otherwise grab its default value
     * 
     * Note: Available only during the property auditing
     */
    public function value()
    {
        $propertyName = $this->propertyName;

        if (!$propertyName) return $this->default;

        return $this->customData->$propertyName ?? $this->default;
    }

    /**
     * Register an action task that will be executed before auditing 
     * a property
     */
    public function addBeforeAuditAction(callable $actionHandler, ?string $groupAction = null)
    {
        $this->beforeAuditActions[$groupAction ?? self::ACTION_GENERAL][] = $actionHandler;

        return $this;
    }

    /**
     * Register an action task that will be executed after auditing 
     * a property
     */
    public function addAfterAuditAction(callable $actionHandler, ?string $groupAction = null)
    {
        $this->afterAuditActions[$groupAction ?? self::ACTION_GENERAL][] = $actionHandler;

        return $this;
    }

    /**
     * Execute all registered actions that need to be run before 
     * auditing a single property
     */
    private function executeBeforeAuditActions()
    {
        foreach (static::$eventExecutionOrders as $groupActionName) {
            foreach (($this->beforeAuditActions[$groupActionName] ?? []) as $action) {
                $action($this);
            }
        }

        return $this;
    }

    /**
     * Execute all registered actions that need to be run after 
     * auditing a single property
     */
    private function executeAfterAuditActions()
    {
        foreach (static::$eventExecutionOrders as $groupActionName) {
            foreach (($this->afterAuditActions[$groupActionName] ?? []) as $action) {
                $action($this);
            }
        }

        return $this;
    }

    /**
     * Set laravel request validation rules
     * 
     * @param callable|array $rules
     */
    public function rules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get laravel rules that can be applied in the FormRequest
     *
     * @return callable|array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Get the type of the property
     */
    public function getType()
    {
        return $this->selectedType;
    }

    /**
     * Get the child type of the property when it is an array
     */
    public function getChildType()
    {
        return $this->childTypeShouldBe;
    }

    /**
     * Get the name of the property
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Set a default value of the current property
     */
    public function default($default = null)
    {
        if ($default === null) return $this;

        $this->default = $default;

        $this->addBeforeAuditAction(function () {
            $this->customData->get($this->propertyName, $this->default);
        });

        return $this;
    }

    /**
     * add the current property among the valiadated
     * ones
     */
    private function registerToValidation()
    {
        $this->customData->setValidatedProperty($this->propertyName);
    }
}
