<?php

namespace Kakaprodo\CustomData\Lib\Base;

use Kakaprodo\CustomData\Lib\CustomDataBase;

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
    public $rules;

    /**
     * carry a function that cast a property to a given type
     */
    protected $cast = null;

    /**
     * property default value
     */
    public $default = null;

    /**
     * validate a given property
     */
    abstract public function validate($propertyName);

    /**
     * Audit a given single property name of the inputed data
     */
    public function audit($propertyName)
    {
        $this->propertyName = $propertyName;

        $this->executeBeforeAuditActions();

        if ($this->canValidateProperty()) $this->validate($propertyName);

        $this->executeAfterAuditActions();

        return $this;
    }

    /**
     * check if a property can be validated
     */
    private function canValidateProperty()
    {
        return $this->selectedType && $this->value();
    }

    /**
     * Grab the value of the current property it it exists,
     * otherwise grab its default value
     * 
     * Note: Available only during the property auditing
     */
    public function value()
    {
        $propertyName = $this->propertyName ?? 'nosignal_property';

        return $this->customData->$propertyName ?? $this->default;
    }

    /**
     * Register an action task that will be executed before auditing 
     * a property
     */
    protected function addBeforeAuditAction(callable $actionHandler)
    {
        $this->beforeAuditActions[] = $actionHandler;

        return $this;
    }

    /**
     * Register an action task that will be executed after auditing 
     * a property
     */
    protected function addAfterAuditAction(callable $actionHandler)
    {
        $this->afterAuditActions[] = $actionHandler;

        return $this;
    }

    /**
     * Execute all registered actions that need to be run before 
     * auditing a single property
     */
    private function executeBeforeAuditActions()
    {
        foreach ($this->beforeAuditActions as $action) {
            $action($this);
        }

        return $this;
    }

    /**
     * Execute all registered actions that need to be run after 
     * auditing a single property
     */
    private function executeAfterAuditActions()
    {
        foreach ($this->afterAuditActions as $action) {
            $action($this);
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
}
