<?php

namespace Kakaprodo\CustomData\Helpers;

use Exception;
use ReflectionMethod;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Exceptions\ActionWithNoArgumentException;
use Kakaprodo\CustomData\Exceptions\ActionHandleMethodNotFoundException;
use Kakaprodo\CustomData\Exceptions\ActionWithNonCustomDataArgumentException;
use Kakaprodo\CustomData\Jobs\QueueCustomDataActionJob;

abstract class CustomActionBuilder
{

    /**
     * the method to call on the class that extends the customData 
     * class
     */
    public static $handleMethod = 'handle';

    /**
     * the queue name on which the action will be dispatched
     * , if null the default queue will be used
     */
    public $onQueue = null;

    /**
     * define whether the action should run in the background
     * after data validation
     */
    public $shouldQueue = false;

    /**
     * instance of the action that is being processed
     * 
     * @var CustomActionBuilder
     */
    static $existingActionInstance = null;

    /**
     * Deefine a custom handler method where
     * data will be passed to
     */
    public static function on($handleMethod)
    {
        static::$handleMethod = $handleMethod;

        return new static;
    }

    /**
     * define that the action should be processed in the background
     * after data validation
     */
    public static function queue($queueName = null)
    {
        $action = (new static());

        static::$existingActionInstance = $action;

        static::$existingActionInstance->shouldQueue($queueName);

        return new static;
    }

    /**
     * set the ability to queue the action on a given name
     */
    protected function shouldQueue($queueName = null)
    {
        $this->shouldQueue = true;
        $this->onQueue = $queueName;
    }

    /**
     * Detect the appropriate CustomData for a given class
     * 
     * @var array|CustomData
     * @var Closure $beforeDataBoot : action to execute before the boot method
     */
    public static function process(
        $data,
        ?callable $beforeDataBoot = null
    ) {
        $action =  self::getActionInstance();

        $customData = $action->dataToInject($action, $data, $beforeDataBoot);

        if ($action->shouldQueue) {
            return QueueCustomDataActionJob::dispatch($action, $customData, static::$handleMethod);
        }

        return $action->{static::$handleMethod}($customData);
    }

    /**
     * check if there is an existing action instance to
     * use and if not create new one
     */
    public static function getActionInstance()
    {
        $action =  (new static());

        $existingActionInstance = static::$existingActionInstance;

        $action = ($existingActionInstance  instanceof $action) ? $existingActionInstance : $action;

        static::$existingActionInstance = null;

        return  $action;
    }

    /**
     * Detect the data to be injected into the action's handler method
     */
    protected function dataToInject(
        CustomActionBuilder $action,
        $data,
        callable $beforeDataBoot = null
    ) {
        if ($data instanceof CustomData) return $data;

        $customDataClass = self::getActionHandleDataClass($action);

        return $customDataClass::make($data, $beforeDataBoot);
    }

    /**
     * Detect the custom data class of the argument of the actual 
     * Action::handle method
     * 
     * @return string
     */
    public static function getActionHandleDataClass(CustomActionBuilder $action)
    {

        if (!method_exists($action, static::$handleMethod)) {
            throw new ActionHandleMethodNotFoundException(
                "Your action " . get_class($action) . " should have a handle method or"
                    . " define a custom one by using ::on(myHandleMehtod)->process([])"
            );
        }

        $actionHandleMethod = new ReflectionMethod($action, static::$handleMethod);
        $actionHandleParams = $actionHandleMethod->getParameters();

        if (!count($actionHandleParams)) {
            throw new ActionWithNoArgumentException(
                "Your action " . get_class($action) . "::handle is supposed to have an arguments"
            );
        }

        $argumentName = ($actionHandleParams[0])->getName();
        $customDataReflection = ($actionHandleParams[0])->getType();

        if (!$customDataReflection) {
            throw new ActionWithNonCustomDataArgumentException(
                "Your action " . get_class($action) . "::handle's argument \${$argumentName} is missing a customData type"
            );
        }

        if ($customDataReflection->isBuiltIn()) {
            throw new ActionWithNonCustomDataArgumentException(
                "Your action " . get_class($action) . "::handle's argument \${$argumentName} should use a custom data type"
            );
        }

        $customDataClass = $customDataReflection->getName();

        if (!(new $customDataClass([]) instanceof CustomData)) {
            throw new ActionWithNonCustomDataArgumentException(
                "The class {$customDataClass} should extend " . CustomData::class
            );
        }

        return $customDataClass;
    }
}
