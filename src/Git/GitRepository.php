<?php

namespace Antares\Support\Git;

class GitRepository
{
    /**
     * Repository real path
     *
     * @var string
     */
    protected $path;

    /**
     * Class constructor
     *
     * @param  string $path
     * @throws GitException
     */
    public function __construct(string $path)
    {
        $this->setPath($path);
    }

    /**
     * Protected path property getter
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Protected property path setter
     *
     * @param string $path
     * @param boolean $checkDotGit
     * @return void
     */
    public function setPath(string $path, bool $checkDotGit = true)
    {
        $path = trim($path);

        if ($path == '') {
            throw GitException::forNoPathSupplied();
        }

        if (basename($path) == '.git') {
            $path = dirname($path);
        }

        $realPath = realpath(rtrim($path, DIRECTORY_SEPARATOR));
        if ($realPath === false) {
            throw GitException::forPathNotFound($path);
        }

        $this->path = $realPath;

        if ($checkDotGit) {
            $this->checkDotGit();
        }
    }

    /**
     * Get dotGit folder
     *
     * @return string
     */
    public function dotGitFolder()
    {
        return $this->path . DIRECTORY_SEPARATOR . '.git';
    }

    /**
     * Check dotGit folder existence
     *
     * @param boolean $throwException
     * @return void
     */
    public function checkDotGit(bool $throwException = true)
    {
        $hasDotGit = is_dir($this->dotGitFolder());

        if (!$hasDotGit and $throwException) {
            throw GitException::forDotGitFolderNotFound($this->path);
        }

        return $hasDotGit;
    }
}
