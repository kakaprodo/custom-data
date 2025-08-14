<?php

namespace Kakaprodo\CustomData;

use Kakaprodo\CustomData\Helpers\FillData;
use Kakaprodo\CustomData\Lib\CustomDataBase;

abstract class CustomData extends CustomDataBase
{
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
        $filledData = self::isCallable($payload) ? FillData::format($payload) : $payload;

        $data =  new static($filledData);

        return  $data->handleLifecycle($beforeBoot);
    }

    protected function handleLifecycle(?callable $beforeBoot = null)
    {
        $this->validateRequiredProperties();

        $this->propertyNameTransformation(); // this will transform only unValidated properties

        if ($beforeBoot) $beforeBoot($this);

        $this->boot();

        return $this;
    }

    public function beforeBoot(callable $callable)
    {
        $callable($this);

        return $this;
    }


    public function boot() {}

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        return $this->data[str_replace('?', '', $name)] = $value;
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
