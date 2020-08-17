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
     * Create a new exception for config file not found
     *
     * @return static
     */
    public static function forConfigFileNotFound()
    {
        return new static('Git config file not found in .git folder.');
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

    /**
     * Create a new exception for invalid resource
     *
     * @param string $resource
     * @return static
     */
    public static function forInvalidResource($resource)
    {
        return new static("Invalid resource '{$resource}'.");
    }

    /**
     * Create a new exception for no change found for resource
     *
     * @param string $resource
     * @return static
     */
    public static function forNoChangeFoundForResource($resource)
    {
        return new static("No change found for resource '{$resource}'.");
    }

    /**
     * Create a new exception for staged resources differs from selected changes
     *
     * @param string $resource
     * @return static
     */
    public static function forStagedResourcesDiffersFromSelectedChanges()
    {
        return new static('Staged resources differs from selected changes to commit.');
    }

    /**
     * Create a new exception for invalid repository name
     *
     * @param string $repository
     * @return static
     */
    public static function forInvalidRepositoryName($repository)
    {
        return new static("Invalid repository name '{$repository}'.");
    }

    /**
     * Create a new exception for invalid tag name
     *
     * @param string $tag
     * @return static
     */
    public static function forInvalidTagName($tag)
    {
        return new static("Invalid tag name '{$tag}'.");
    }
}
