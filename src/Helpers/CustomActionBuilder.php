<?php

namespace Kakaprodo\CustomData\Helpers;

use Exception;
use ReflectionMethod;
use Kakaprodo\CustomData\CustomData;

abstract class CustomActionBuilder
{
    public static function process(
        array $data,
        ?callable $beforeDataBoot = null
    ) {
        $action = (new static());

        if (!method_exists($action, 'handle')) {
            throw new Exception(
                "Your action " . get_class($action) . " should have a handle method"
            );
        }

        $customDataClass = self::getActionHandleDataClass($action);

        $customData = $customDataClass::make(...func_get_args());

        return $action->handle($customData, $beforeDataBoot);
    }

    /**
     * Detect the custom data class of the argument of the actual 
     * Action::handle method
     * 
     * @return string
     */
    public static function getActionHandleDataClass(CustomActionBuilder $action)
    {

        $actionHandleMethod = new ReflectionMethod($action, 'handle');
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
