<?php

namespace Antares\Support\Git;

class GitFetch extends GitCommand
{
    /**
     * Do run
     *
     * @param string $commandOptions
     * @return bool
     */
    protected function doRun($commandOptions)
    {
        if ($this->command != 'fetch') {
            $this->command = 'fetch';
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
        $params['command'] = 'fetch';
        return parent::make($params);
    }
}
