<?php

namespace Kakaprodo\CustomData;

use Exception;
use Kakaprodo\CustomData\Lib\CustomDataBase;
use Kakaprodo\CustomData\Lib\TypeHub\DataTypeHub;
use Kakaprodo\CustomData\Traits\HasCustomDataHelper;

abstract class CustomData extends CustomDataBase
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

        if ($value || !$default) return $value;

        $this->{$property} = $default;

        return $default;
    }
}
