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
}
