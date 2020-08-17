<?php

namespace Antares\Support\Git;

class GitCommit extends GitCommand
{
    /**
     * Do run
     *
     * @param string $commandOptions
     * @return bool
     */
    protected function doRun($commandOptions)
    {
        if ($this->command != 'commit') {
            $this->command = 'commit';
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
        $params['command'] = 'commit';
        return parent::make($params);
    }
}
