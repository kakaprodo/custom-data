<?php

namespace Kakaprodo\CustomData\Helpers;


class Optional
{
    protected $object = null;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function __call($name, $arguments)
    {
        if (!$this->object || !is_object($this->object)) return null;

        return method_exists($this->object, $name) ?
            $this->object->{$name}($arguments)
            : null;
    }

    public function __get($name)
    {
        if (!$this->object || !is_object($this->object)) return null;

        $value = $this->object->$name;

        return  isset($value) ? $value : null;
    }
}
