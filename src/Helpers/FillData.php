<?php

namespace Kakaprodo\CustomData\Helpers;

use Illuminate\Database\Eloquent\Model;
use Kakaprodo\CustomData\CustomData;

/**
 * Fill the expected data with a given object
 */
class FillData
{
    /**
     * any value from which a property will be filled from
     */
    protected $value = null;

    /**
     * set any value to fill from the 
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public static function format(callable $callback): array
    {
        $payload = $callback(new self());

        $data = [];
        foreach ($payload as $property => $value) {
            $data[$property] = $value instanceof FillData ? self::castValueToArray($value) : $value;
        }

        return $data;
    }

    public static function castValuetoArray(FillData $fillData)
    {
        $value = $fillData->value;

        if ($value instanceof Model) return $value->toArray();

        if ($value instanceof CustomData) return $value->onlyValidated();

        // TODO : more types are coming

        return $value;
    }

    /**
     * fill property from any object 
     */
    public function from($object)
    {
        return (new self())->setValue($object);
    }
}
