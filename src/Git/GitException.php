<?php

namespace Antares\Support\Git;

use Exception;

class GitException extends Exception
{
    /**
     * Create a new exception for no path supplied
     *
     * @return static
     */
    public static function forNoPathSupplied()
    {
        return new static('No path supplied.');
    }

    /**
     * Create a new exception for path not found
     *
     * @param string $path
     * @return static
     */
    public static function forPathNotFound($path)
    {
        return new static("Path not found: '{$path}'.");
    }

    /**
     * Create a new exception for dotGit folder not found in path
     *
     * @param string $path
     * @return static
     */
    public static function forDotGitFolderNotFound($path)
    {
        return new static("Git folder (.git) not found in '{$path}'.");
    }

    /**
     * Create a new exception for failed command execution
     *
     * @param string $command
     * @param int $exitCode
     * @return static
     */
    public static function forFailedCommandExecution($command, $exitCode)
    {
        return new static("Command '{$command}' execution failed with exit code '{$exitCode}'.");
    }
}
