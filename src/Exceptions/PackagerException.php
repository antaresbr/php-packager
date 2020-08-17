<?php

namespace Antares\Support\Packager\Exceptions;

use Exception;

class PackagerException extends Exception
{
    /**
     * Create a new exception for infos not found
     *
     * @return static
     */
    public static function forInfosNotFound()
    {
        return new static("File infos.json not found.\n");
    }

    /**
     * Create a new exception for invalid version value
     *
     * @return static
     */
    public static function forInvalidVersionValue($value)
    {
        if (is_array($value)) {
            $value = print_r($value, true);
        }
        return new static("Invalid version value: '{$value}'.\n");
    }
}
