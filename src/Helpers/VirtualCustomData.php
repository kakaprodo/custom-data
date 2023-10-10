<?php

namespace Kakaprodo\CustomData\Helpers;

use Kakaprodo\CustomData\CustomData;
use Kakaprodo\CustomData\Exceptions\VirtualCustomDataFieldException;

class VirtualCustomData extends CustomData
{
    public $virtualProperties = [];

    public $errorHandling = null;

    protected function expectedProperties(): array
    {
        return $this->virtualProperties;
    }

    /**
     * validate array properties then return only validated fields
     * 
     * @param callable myValidationClosure : where to define your expectedProperties
     * @param array $data : the inputed data
     * @param callable $errors : where you can catch errors that happening
     * 
     */
    public static  function  check(
        callable $myValidationClosure,
        $data = [],
        callable $errors = null
    ): CustomData {
        $virtual = new static($data);

        $properties = $myValidationClosure($virtual);
        $virtual->errorHandling = $errors;

        if (!is_array($properties)) {
            $virtual->throwError(
                "The first argument should be a closure that returns an array",
                VirtualCustomDataFieldException::class
            );
        }

        $virtual->virtualProperties = $properties;

        $customData = $virtual->handleLifecycle();

        return  $customData;
    }

    /**
     * a magic custom data method that consume all error thrown by customData
     */
    public function customErrorHandling($message, \Throwable $th)
    {
        if ($this->errorHandling) return $this->callFunction(
            $this->errorHandling,
            null,
            ...func_get_args()
        );

        throw $th;
    }
}
