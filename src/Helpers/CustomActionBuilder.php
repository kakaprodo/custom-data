<?php

namespace Kakaprodo\CustomData\Helpers;

use Exception;
use ReflectionMethod;
use Kakaprodo\CustomData\CustomData;

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

    public static function process(
        array $data,
        ?callable $beforeDataBoot = null
    ) {
        $action = (new static());

        if (!method_exists($action, static::$handleMethod)) {
            throw new Exception(
                "Your action " . get_class($action) . " should have a handle method or define a cutom one by using ::on(myHandleMehtod)->process([])"
            );
        }

        $customDataClass = self::getActionHandleDataClass($action);

        $customData = $customDataClass::make(...func_get_args());

        return $action->{static::$handleMethod}($customData, $beforeDataBoot);
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
            throw new Exception(
                "Your action " . get_class($action) . "::handle is supposed to have an arguments"
            );
        }

        $argumentName = ($actionHandleParams[0])->getName();
        $customDataReflection = ($actionHandleParams[0])->getType();

        if (!$customDataReflection) {
            throw new Exception(
                "Your action " . get_class($action) . "::handle's argument \${$argumentName} is missing a customData type"
            );
        }

        if ($customDataReflection->isBuiltIn()) {
            throw new Exception(
                "Your action " . get_class($action) . "::handle's argument \${$argumentName} should use a custom data type"
            );
        }

        $customDataClass = $customDataReflection->getName();

        if (!(new $customDataClass([]) instanceof CustomData)) {
            throw new Exception(
                "The class {$customDataClass} should extend " . CustomData::class
            );
        }

        return $customDataClass;
    }
}
