<?php

namespace Antares\Support\Git;

use Antares\Foundation\Str;

class GitTag extends GitCommand
{
    /**
     * Do run
     *
     * @param string $commandOptions
     * @return bool
     */
    protected function doRun($commandOptions)
    {
        if (!Str::csIn($this->command, 'tag', 'ls-remote')) {
            $this->command = 'tag';
        }

        return parent::doRun($commandOptions);
    }

    /**
     * Get local tag list
     *
     * @return array
     */
    public function getLocalList()
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
    public function getRemoteList()
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
                    $tag = substr($line, $pos + $needleLength);
                    if (strpos($tag, '^') === false) {
                        $tags[] = substr($line, $pos + $needleLength);
                    }
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
    public function getList(bool $remote = false)
    {
        return $remote ? $this->getRemoteList() : $this->getLocalList();
    }

    /**
     * Get tags from remote
     *
     * @return void
     */
    public function fetchFromRemote()
    {
        GitFetch::make([
            'repository' => $this->repository(),
            'options' => ['--tags'],
            'showCommand' => $this->showCommand,
            'showOutput' => $this->showOutput,
        ])->run();
    }

    /**
     * Add annotated tag
     *
     * @param string $tag
     * @param string $message
     * @return void
     */
    public function add(string $tag, string $message)
    {
        $message = trim($message);
        if ($message == '') {
            $message = $tag;
        }

        $this->command = 'tag';
        $this->options = [
            '--annotate',
            ['--message' => $message],
            $tag,
        ];
        $this->run();
    }

    /**
     * Push tag to repository name
     *
     * @param string $repositoryName
     * @param string $tagName
     * @return void
     */
    public function push(string $tagName, string $repositoryName = 'origin')
    {
        $tagName = trim($tagName);
        if (empty($tagName) or !in_array($tagName, $this->getLocalList())) {
            throw GitException::forInvalidRepositoryName($tagName);
        }

        GitPush::make([
            'repository' => $this->repository(),
            'options' => [$repositoryName, $tagName],
            'showCommand' => $this->showCommand,
            'showOutput' => $this->showOutput,
        ])->run();
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
