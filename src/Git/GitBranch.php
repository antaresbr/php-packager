<?php

namespace Antares\Support\Git;

class GitBranch extends GitCommand
{
    /**
     * Get current branch
     *
     * @param bool $short
     * @return str
     */
    public function getCurrent(bool $short = true)
    {
        $this->command = 'symbolic-ref';
        $this->options = [];
        if ($short) {
            $this->options[] = '--short';
        }
        $this->options[] = 'HEAD';
        $r = $this->run()->output;
        return !empty($r) ? $r[0] : '';
    }

    /**
     * Make a new instance of this class based on parameters
     *
     * @param array $params
     * @return static
     */
    public static function make(array $params = [])
    {
        $params['command'] = 'branch';
        return parent::make($params);
    }
}
