<?php

namespace Antares\Support\Git;

class GitTagList extends GitCommand
{
    /**
     * Get local tag list
     *
     * @return array
     */
    public function getLocalTags()
    {
        $this->command = 'tag';
        $this->options = ['--list'];
        return $this->run()->output;
    }

    /**
     * Get remote tag list
     *
     * @return array
     */
    public function getRemoteTags()
    {
        $this->command = 'ls-remote';
        $this->options = ['--quiet', '--tags'];
        $this->run();

        $tags = [];
        if (!empty($this->output)) {
            $needle = 'refs/tags/';
            $needleLength = strlen($needle);
            foreach ($this->output as $line) {
                $pos = strpos($line, $needle);
                if ($pos !== false) {
                    $tags[] = substr($line, $pos + $needleLength);
                }
            }
        }
        $this->output = $tags;

        return $tags;
    }

    /**
     * Get tag list
     *
     * @param boolean $remote
     * @return array
     */
    public function get(bool $remote = false)
    {
        return $remote ? $this->getRemoteTags() : $this->getLocalTags();
    }

    /**
     * Make a new instance of this class based on parameters
     *
     * @param array $params
     * @return static
     */
    public static function make(array $params = [])
    {
        $params['command'] = 'tag';
        return parent::make($params);
    }
}
