<?php

namespace Kakaprodo\CustomData;

use Exception;
use Kakaprodo\CustomData\Traits\HasCustomDataHelper;

abstract class CustomData
{
    use HasCustomDataHelper;

    protected array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * create data request instance
     */
    public static function make(array $data, ?callable $beforeBoot = null)
    {
        $data =  new static($data);

        $data->validateRequiredProperties();

        if ($beforeBoot) $beforeBoot($data);

        $data->boot();

        return $data;
    }

    public function beforeBoot(callable $callable)
    {
        $callable($this);

        return $this;
    }


    public function boot()
    {
    }

    /**
     * Required  class properties 
     * 
     * Note: when defining property, use  the suffix `?` to 
     * your property for defining it as optional
     */
    abstract protected function expectedProperties(): array;

    /**
     * All data passed to the class
     */
    public function all(): array
    {
        return $this->data;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Check if all required properties were provided
     */
    private function validateRequiredProperties()
    {

        foreach ($this->expectedProperties() as $property) {

            // if a property is optional
            if ($this->strEndsWith($property, '?')) continue;

            if ($this->{$property} == null) throw new Exception(
                "The property {$property} is required on the class " . static::class
            );
        }
    }
}
