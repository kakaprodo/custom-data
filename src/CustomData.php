<?php

namespace Kakaprodo\CustomData;

use Kakaprodo\CustomData\Helpers\FillData;
use Kakaprodo\CustomData\Lib\CustomDataBase;

abstract class CustomData extends CustomDataBase
{
    /**
     * kept incoming data and data that will be
     * set at runtime
     */
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * create data request instance
     */
    public static function make(
        $payload,
        ?callable $beforeBoot = null
    ) {
        $filledData = is_callable($payload) ? FillData::format($payload) : $payload;

        $data =  new static($filledData);

        return  $data->handleLifecycle($beforeBoot);
    }

    protected function handleLifecycle(?callable $beforeBoot = null)
    {
        $this->validateRequiredProperties();

        $this->propertyNameTransformation();

        if ($beforeBoot) $beforeBoot($this);

        $this->boot();

        return $this;
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

    public function __set($name, $value)
    {
        return $this->data[str_replace('?', '', $name)] = $value;
    }

    /**
     * get a given property with the ability to pass
     * a default in case the property is not defined
     */
    public function get($property, $default = null)
    {
        $value = $this->{$property};

        if (isset($value) || $default === null) return $value;

        $this->{$property} = $default;

        return $default;
    }

    /**
     * All validated properties
     */
    public function onlyValidated(): array
    {
        return $this->validatedProperties;
    }

    public function __toString()
    {
        return json_encode($this->unserializeValidated());
    }

    public function __toArray()
    {
        return $this->unserializeValidated();
    }
}
