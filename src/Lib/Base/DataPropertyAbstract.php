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
     * validate a given property
     */
    abstract public function validate($propertyName);

    /**
     * Audit a given property name of the inputed data
     */
    public function audit($propertyName)
    {
        $this->propertyName = $propertyName;

        $this->executeBeforeAuditActions();

        if ($this->selectedType) $this->validate($propertyName);

        $this->executeAfterAuditActions();

        return $this;
    }

    /**
     * Grab the value of the current property.
     * Note: Available only during the property auditing
     */
    public function value()
    {
        $propertyName = $this->propertyName ?? 'nosignal_property';

        return $this->customData->$propertyName;
    }

    /**
     * Register an action task that will be executed after auditing 
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
     * Execute all registered actions
     */
    private function executeBeforeAuditActions()
    {
        foreach ($this->beforeAuditActions as $action) {
            $action();
        }

        return $this;
    }

    /**
     * Execute all registered actions
     */
    private function executeAfterAuditActions()
    {
        foreach ($this->afterAuditActions as $action) {
            $action();
        }

        return $this;
    }
}
