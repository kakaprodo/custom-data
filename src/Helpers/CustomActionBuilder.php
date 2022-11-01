<?php

namespace Kakaprodo\CustomData\Helpers;

use Exception;
use ReflectionMethod;
use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Exceptions\ActionWithNoArgumentException;
use Kakaprodo\CustomData\Exceptions\ActionHandleMethodNotFoundException;
use Kakaprodo\CustomData\Exceptions\ActionWithNonCustomDataArgumentException;

abstract class CustomActionBuilder
{

    /**
     * the method to call on the class that extends the customData 
     * class
     */
    public static $handleMethod = 'handle';

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
     * Detect the appropriate CustomData for a given class
     * 
     * @var array|CustomData
     * @var Closure $beforeDataBoot : action to execute before the boot method
     */
    public static function process(
        $data,
        ?callable $beforeDataBoot = null
    ) {
        $action = (new static());

        if (!method_exists($action, static::$handleMethod)) {
            throw new ActionHandleMethodNotFoundException(
                "Your action " . get_class($action) . " should have a handle method or"
                    . " define a custom one by using ::on(myHandleMehtod)->process([])"
            );
        }

        $customData = $action->dataToInject($action, $data, $beforeDataBoot);

        return $action->{static::$handleMethod}($customData);
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
