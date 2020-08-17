<?php

namespace Antares\Support\Git;

use Antares\Support\Str;

class GitStatus extends GitCommand
{
    /**
     * Do run
     *
     * @param string $commandOptions
     * @return bool
     */
    protected function doRun($commandOptions)
    {
        if (!Str::csIn($this->command, 'status', 'update-index', 'diff')) {
            $this->command = 'status';
        }

        return parent::doRun($commandOptions);
    }

    /**
     * Update git index
     *
     * @return void
     */
    public function updateIndex()
    {
        $this->command = 'update-index';
        $this->options = ['-q', '--refresh'];
        $this->run();
    }

    /**
     * Get local changes
     *
     * @param array $resources
     * @return array
     */
    public function getLocalChanges(array $resources = [])
    {
        $this->updateIndex();

        $this->command = 'status';
        $this->options = array_merge(['--porcelain'], $resources);
        return $this->run()->output;
    }

    /**
     * Get staged resources
     *
     * @return array
     */
    public function getStagedResources()
    {
        $this->updateIndex();

        $this->command = 'diff';
        $this->options = ['--staged', '--name-only'];
        return $this->run()->output;
    }

    /**
     * Make a new instance of this class based on parameters
     *
     * @param array $params
     * @return static
     */
    public static function make(array $params = [])
    {
        $params['command'] = 'status';
        return parent::make($params);
    }
}
