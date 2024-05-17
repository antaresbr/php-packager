<?php

namespace Antares\Support\Git;

use Antares\Foundation\Options\Options;

class GitCommand
{
    /**
     * Repository object
     *
     * @var GitRepository
     */
    protected $repository;

    /**
     * Protected property repository accessor
     *
     * @return GitRepository
     */
    public function repository()
    {
        if ($this->repository == null) {
            $this->repository = new GitRepository(getcwd());
        }
        return $this->repository;
    }

    /**
     * Git command name
     *
     * @var string
     */
    protected $command;

    /**
     * Git command options
     *
     * @var array
     */
    protected $options;

    /**
     * Property 'options' acessor
     *
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Property 'options' setter
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Convert an option item to shell option
     *
     * @param mixed $key
     * @param mixed $value
     * @return string
     */
    private function itemToShellOption($key, $value)
    {
        if (is_array($value)) {
            return $this->arrayToShellOption($key, $value);
        } elseif (!is_bool($value) and is_scalar($value)) {
            return (is_string($key) ? "{$key} " : '') . escapeshellarg($value);
        }
        return '';
    }

    /**
     * Convert an array option to shell option
     *
     * @param mixed $key
     * @param mixed $value
     * @return string
     */
    private function arrayToShellOption($key, $value)
    {
        $options = [];

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $options[] = $this->itemToShellOption($k, $v);
            }
            if (is_string($key) and !empty($options)) {
                array_unshift($options, $key);
            }
        } else {
            $options[] = $this->itemToShellOption($key, $value);
        }

        return implode(' ', $options);
    }

    /**
     * Get shell command options from options property and extras
     *
     * @param array $extras
     * @return string
     */
    public function toShellOptions(array $extras = [])
    {
        return $this->arrayToShellOption(null, array_merge($this->options, $extras));
    }

    /**
     * Last command execution exit code
     *
     * @var int
     */
    public $exitCode;

    /**
     * Last command execution output
     *
     * @var array
     */
    public $output;

    /**
     * Flag to execute interactively
     *
     * @var boolean
     */
    public $interactive = false;

    /**
     * Flag to show command line on execution
     *
     * @var boolean
     */
    public $showCommand = false;

    /**
     * Flag to show output after command execution
     *
     * @var boolean
     */
    public $showOutput = true;

    /**
     * Do show output array
     *
     * @return void
     */
    public function showOutput()
    {
        if ($this->showOutput and !empty($this->output)) {
            foreach ($this->output as $line) {
                echo "{$line}\n";
            }
        }
    }

    /**
     * Do run
     *
     * @param string $commandOptions
     * @return bool
     */
    protected function doRun($commandOptions)
    {
        $this->output = [];
        $this->exitCode = 0;

        $cmd = "git {$this->command} {$commandOptions}";

        if ($this->showCommand) {
            echo "\n";
            echo "{$cmd}\n";
        }

        exec($cmd . ($this->interactive ? '' : ' 2>&1'), $this->output, $this->exitCode);

        $this->showOutput();

        if ($this->exitCode !== 0) {
            throw GitException::forFailedCommandExecution($cmd, $this->exitCode);
        }

        return ($this->exitCode === 0);
    }

    /**
     * Run command
     *
     * @return static
     */
    public function run()
    {
        $currentDir = getcwd();

        try {
            chdir($this->repository()->getPath());
            $this->doRun($this->toShellOptions(func_get_args()));
        } finally {
            chdir($currentDir);
        }

        return $this;
    }

    /**
     * Make a new instance of this class based on parameters
     *
     * @param array $params
     * @return static
     */
    public static function make(array $params = [])
    {
        $args = Options::make($params, [
            'repository' => ['type' => 'Antares\Support\Git\GitRepository', 'default' => null],
            'command' => ['type' => 'string', 'required' => true],
            'options' => ['type' => 'array', 'default' => []],
            'interactive' => ['type' => 'boolean', 'default' => false],
            'showCommand' => ['type' => 'boolean', 'default' => false],
            'showOutput' => ['type' => 'boolean', 'default' => true],
        ])->validate();

        $r = new static();
        $r->repository = $args->repository;
        $r->command = $args->command;
        $r->options = $args->options;
        $r->interactive = $args->interactive;
        $r->showCommand = $args->showCommand;
        $r->showOutput = $args->showOutput;

        return $r;
    }
}
