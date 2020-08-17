<?php

namespace Antares\Support\Git;

class GitPush extends GitCommand
{
    /**
     * Do run
     *
     * @param string $commandOptions
     * @return bool
     */
    protected function doRun($commandOptions)
    {
        if ($this->command != 'push') {
            $this->command = 'push';
        }

        if (empty($this->options)) {
            $this->options = ['origin'];
        }

        $repositoryName = $this->options[array_key_first($this->options)];
        if (empty($repositoryName) or $this->repository()->configHas('[remote "' . $repositoryName . '"]') === false) {
            throw GitException::forInvalidRepositoryName($repositoryName);
        }

        return parent::doRun($commandOptions);
    }

    /**
     * Make a new instance of this class based on parameters
     *
     * @param array $params
     * @return static
     */
    public static function make(array $params = [])
    {
        $params['command'] = 'push';
        return parent::make($params);
    }
}
