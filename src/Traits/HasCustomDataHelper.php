<?php

namespace Kakaprodo\CustomData\Traits;


trait HasCustomDataHelper
{
    /**
     * Check if a string ends with a given string
     */
    public function strEndsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (
                $needle !== '' && $needle !== null
                && substr($haystack, -strlen($needle)) === (string) $needle
            ) {
                return true;
            }
        }

        return false;
    }
}
