<?php

namespace Antares\Support\Git;

use Antares\Support\Str;

class GitChangesHandler
{
    /**
     * Working repository
     *
     * @var GitRepository
     */
    private $repository;

    /**
     * Acessor for repository property
     *
     * @return GitRepository
     */
    private function repository()
    {
        if ($this->repository == null) {
            $this->repository = new GitRepository(getcwd());
        }
        return $this->repository;
    }

    /**
     * Working GitStatus object
     *
     * @var GitStatus
     */
    private $gitStatus;

    /**
     * Acessor for gitStats property
     *
     * @return GitStatus
     */
    private function gitStatus()
    {
        if ($this->gitStatus == null) {
            $this->gitStatus = GitStatus::make([
                'repository' => $this->repository(),
                'showCommand' => false,
                'showOutput' => false,
            ]);
        }
        return $this->gitStatus;
    }

    /**
     * Changes array
     *
     * @var array
     */
    protected $changes = [];

    /**
     * Protected changes property acessor
     *
     * @return array
     */
    public function changes()
    {
        return $this->changes;
    }

    /**
     * Check if this handler has changes
     *
     * @return bool
     */
    public function hasChanges()
    {
        return !empty($this->changes);
    }

    /**
     * Get a resource change
     *
     * @param string $resource
     * @return array|bool
     */
    public function getChange(string $resource)
    {
        foreach ($this->changes as $change) {
            if ($change['resource'] == $resource) {
                return $change;
            }
        }

        return false;
    }

    /**
     * Check if it has a resource change
     *
     * @param string $resource
     * @return bool
     */
    public function hasChange(string $resource)
    {
        foreach ($this->changes as $change) {
            if ($change['resource'] == $resource) {
                return true;
            }
        }

        return false;
    }

    /**
     * Select a resource change
     *
     * @param string $resource
     * @param bool $selected
     * @return void
     */
    public function selectChange(string $resource, bool $selected = true)
    {
        foreach ($this->changes as &$change) {
            if ($change['resource'] == $resource) {
                $change['selected'] = $selected ? 'X' : '';
                return;
            }
        }

        throw GitException::forNoChangeFoundForResource($resource);
    }

    /**
     * Check if it has selected resources
     *
     * @return bool
     */
    public function hasSelectedChanges()
    {
        foreach ($this->changes as $change) {
            if ($change['selected'] == 'X') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get selected changes array
     *
     * @return array
     */
    public function getSelectedChanges()
    {
        $changes = [];
        foreach ($this->changes as $change) {
            if ($change['selected'] == 'X') {
                $changes[] = $change;
            }
        }
        return $changes;
    }

    /**
     * Get selected change resources array
     *
     * @return array
     */
    public function getSelectedResources()
    {
        $resources = [];
        foreach ($this->getSelectedChanges() as $change) {
            $resources[] = $change['resource'];
        }
        return $resources;
    }

    /**
     * Extract change from a raw line
     *
     * @param string $line
     * @return void
     */
    private function extractChangeFromLine(string $line)
    {
        $resource = trim(substr($line, 3));
        $change = [
            'selected' => '',
            'type' => '',
            'resource' => $resource,
        ];

        switch (strtoupper(trim(substr($line, 0, 2)))) {
            case 'M':
                $change['type'] = 'Modified';
                break;

            case 'D':
                $change['type'] = 'DELETED';
                break;

            case 'R':
                $change['type'] = 'Renamed';
                break;

            default:
                $change['type'] = 'New';
                break;
        }

        if (
            is_null($resource) or
            $resource == '' or
            (!Str::scIn($change['type'], 'DELETED', 'Renamed') and !file_exists($resource))
        ) {
            throw GitException::forInvalidResource($resource);
        }

        return $change;
    }

    /**
     * Add Change
     *
     * @return bool
     */
    public function addChange(string $resource, bool $selected = false)
    {
        if (empty($resource)) {
            throw GitException::forInvalidResource($resource);
        }

        $rawChanges = $this->gitStatus()->getLocalChanges([$resource]);

        if (empty($rawChanges)) {
            throw GitException::forNoChangeFoundForResource($resource);
        }

        foreach ($rawChanges as $line) {
            $change = $this->extractChangeFromLine($line);
            $change['selected'] = $selected ? 'X' : '';

            $this->changes[] = $change;
        }

        return !empty($rawChanges);
    }

    /**
     * Get local changes
     *
     * @return array
     */
    public function getLocalChanges()
    {
        $rawChanges = $this->gitStatus()->getLocalChanges();

        if (!empty($rawChanges)) {
            $modified = [];
            $deleted = [];
            $new = [];
            foreach ($rawChanges as $line) {
                $change = $this->extractChangeFromLine($line);
                if ($change['type'] == 'Modified') {
                    $modified[] = $change;
                } elseif ($change['type'] == 'DELETED') {
                    $deleted[] = $change;
                } else {
                    $new[] = $change;
                }
            }
            usort($modified, function ($a, $b) { return $a['resource'] <=> $b['resource']; });
            usort($deleted, function ($a, $b) { return $a['resource'] <=> $b['resource']; });
            usort($new, function ($a, $b) { return $a['resource'] <=> $b['resource']; });

            $this->changes = array_merge($modified, $deleted, $new);
        }

        return $this->changes;
    }

    /**
     * Get formated changes list
     *
     * @return array
     */
    public function formatedChanges()
    {
        $formated = [];

        if ($this->hasChanges()) {
            $csSeleted = 8;
            $csIndex = 5;
            $csType = 8;
            $csResource = 0;
            foreach ($this->changes as $change) {
                if ($csResource < strlen($change['resource'])) {
                    $csResource = strlen($change['resource']);
                }
            }

            $formated[] = Str::join('---', str_pad('', $csSeleted, '-'), str_pad('', $csIndex, '-'), str_pad('', $csType, '-'), str_pad('', $csResource, '-'));
            $formated[] = 'Selected | Index | Type     | Resource ';
            $formated[] = Str::join('-+-', str_pad('', $csSeleted, '-'), str_pad('', $csIndex, '-'), str_pad('', $csType, '-'), str_pad('', $csResource, '-'));

            foreach ($this->changes as $index => $change) {
                $formated[] = Str::join(
                    ' | ',
                    str_pad(empty($change['selected']) ? '.' : $change['selected'], $csSeleted, ' ', STR_PAD_BOTH),
                    str_pad($index, $csIndex, ' ', STR_PAD_BOTH),
                    str_pad($change['type'], $csType, ' ', STR_PAD_RIGHT),
                    str_pad($change['resource'], $csResource, ' ', STR_PAD_RIGHT)
                );
            }
            $formated[] = Str::join('---', str_pad('', $csSeleted, '-'), str_pad('', $csIndex, '-'), str_pad('', $csType, '-'), str_pad('', $csResource, '-'));
        }

        return $formated;
    }

    /**
     * Show formated changes
     *
     * @return void
     */
    public function showFormated()
    {
        echo PHP_EOL;
        echo "Changes:\n";
        echo implode(PHP_EOL, $this->formatedChanges());
        echo PHP_EOL;
    }

    /**
     * Select changes
     *
     * @return void
     */
    public function doSelection()
    {
        if (!$this->hasChanges()) {
            return;
        }

        $maxIndex = count($this->changes) - 1;
        $finish = false;
        while ($finish == false) {
            $this->showFormated();
            echo "(a)Select all    (+[index])Select    (d)Done/Finish\n";
            echo "(z)Unselect all  (-[index])Unselect\n";
            $options = trim(readline('Options (espace separated): '));
            if ($options == 'ad' or $options == 'da') {
                $options = 'a d';
            }
            $options = explode(' ', $options);

            foreach ($options as $option) {
                $option = strtolower(trim($option));
                $action = Str::csIn(substr($option, 0, 1), '+', '-') ? substr($option, 0, 1) : $option;
                switch ($action) {
                    case 'd':
                    case 'done':
                    case 'finish':
                        $finish = true;
                        break;

                    case 'a':
                    case 'all':
                        foreach ($this->changes as &$change) {
                            $change['selected'] = 'X';
                        }
                        break;

                    case 'z':
                    case 'none':
                        foreach ($this->changes as &$change) {
                            $change['selected'] = '';
                        }
                        break;

                    case '+':
                    case '-':
                        $index = substr($option, 1);
                        if (filter_var($index, FILTER_VALIDATE_INT) === false) {
                            $index = '';
                        } else {
                            $index = (int) $index;
                        }
                        if (is_int($index) and $index >= 0 and $index <= $maxIndex) {
                            $this->changes[$index]['selected'] = ($action == '+') ? 'X' : '';
                        } else {
                            echo "invalid index: {$option}\n";
                        }
                        break;

                    default:
                        echo "invalid option: {$option}\n";
                        break;
                }
            }

            if ($finish and count($options) > 1) {
                $this->showFormated();
            }
        }
    }

    /**
     * Commit message array
     *
     * @var array
     */
    protected $commitMessage = [];

    /**
     * Protected commitMessage property acessor
     *
     * @return array
     */
    public function commitMessage()
    {
        return $this->commitMessage;
    }

    /**
     * Protected commitMessage property setter
     *
     * @return void
     */
    public function setCommitMessage(array $message)
    {
        $this->commitMessage = $message;
    }

    /**
     * Check if this object has a commit message
     *
     * @return bool
     */
    public function hasCommitMessage()
    {
        foreach ($this->commitMessage as $line) {
            $line = trim($line);
            $line = trim($line, "\"\'");
            if ($line != '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Input commit message
     *
     * @return void
     */
    public function inputCommitMessage()
    {
        $this->commitMessage = [];

        if (!$this->hasChanges()) {
            return;
        }

        $message = [];
        $showHeader = true;
        $showHelp = false;
        $action = '';
        while (!Str::icIn($action, 'done', 'abort')) {
            if ($showHeader) {
                echo "\n";
                echo "Enter a commit message. ? = Help\n";
                $showHeader = false;
            }
            if ($showHelp) {
                echo "+-------------------------------------------+\n";
                echo "| A line with:                              |\n";
                echo "|   done  : Confirm message                 |\n";
                echo "|   show  : Show the message defined so far |\n";
                echo "|   reset : Clean and restart message       |\n";
                echo "|   abort : Abort message definition        |\n";
                echo "|   ?     : Show this help                  |\n";
                echo "+-------------------------------------------+\n";
                $showHelp = false;
            }

            $line = readline((count($message) + 1) . ' > ');

            $action = strtolower(trim($line));
            switch ($action) {
                case 'done':
                    $this->commitMessage = $message;
                    break;

                case 'show':
                    echo "\n";
                    foreach ($message as $idx => $text) {
                        $i = $idx + 1;
                        $text = (($i < 10) ? ' ' : '') . $i . ' : ' . $text . "\n";
                        echo $text;
                    };
                    echo "\n";
                break;

                case 'reset':
                    $message = [];
                    echo "*clean*\n";
                    break;

                case 'abort':
                    break;

                case '?':
                case 'help':
                    $showHelp = true;
                    break;

                default:
                    $action = '';
                    break;
            }
            if (!empty($action)) {
                continue;
            }

            $message[] = rtrim($line);
        }
        echo "\n";
    }

    /**
     * Unstage all staged resources
     *
     * @return void
     */
    public function unstageResources()
    {
        // $git = GitCommand::make([
        //     'repository' => $this->repository(),
        //     'command' => 'restore',
        //     'showCommand' => false,
        //     'showOutput' => false,
        // ]);
        // foreach ($this->gitStatus()->getStagedResources() as $resource) {
        //     $git->setOptions(['--staged', $resource]);
        //     $git->run();
        // }

        $git = GitCommand::make([
            'repository' => $this->repository(),
            'command' => 'reset',
            'showCommand' => false,
            'showOutput' => false,
        ])->run();
    }

    /**
     * Stage selected changes
     *
     * @return void
     */
    public function stageSelectedChanges()
    {
        $this->unstageResources();

        if ($this->hasSelectedChanges()) {
            $git = GitCommand::make([
                'repository' => $this->repository(),
                'command' => 'add',
                'showCommand' => false,
                'showOutput' => false,
            ]);

            foreach ($this->getSelectedResources() as $resource) {
                $git->setOptions([$resource]);
                $git->run();
            }
        }
    }

    /**
     * Commit selected changes
     *
     * @return void
     */
    public function commit()
    {
        if (!$this->hasSelectedChanges()) {
            return;
        }

        $selectedResources = $this->getSelectedResources();
        $stagedResources = $this->gitStatus()->getStagedResources();

        $delta = array_diff($selectedResources, $stagedResources);

        //-- ignore renamed from delta
        $oldRenamedResources = [];
        $newRenamedResources = [];
        foreach ($this->gitStatus()->getRenamedResources() as $line) {
            $change = $this->extractChangeFromLine($line);
            if ($change['type'] == 'Renamed') {
                $resource = explode('->', $change['resource']);
                if (count($resource) == 2) {
                    $oldRenamedResources[] = trim($resource[0]);
                    $newRenamedResources[] = trim($resource[1]);
                }
            }
        }
        if (!empty($oldRenamedResources)) {
            $delta = array_diff($delta, $oldRenamedResources);
        }

        if (!empty($delta)) {
            $stagedResources = ', ' . implode(', ', $stagedResources);
            foreach ($delta as $resource) {
                if (!Str::endsWith($resource, '/') or strpos($stagedResources, ', ' . $resource) === false) {
                    $this->unstageResources();
                    throw GitException::forStagedResourcesDiffersFromSelectedChanges();
                }
            }
        }

        GitCommit::make([
            'repository' => $this->repository(),
            'options' => ['--message' => implode("\n", $this->commitMessage())],
            'showCommand' => false,
            'showOutput' => false,
        ])->run();
    }

    /**
     * Push local commits
     *
     * @param string $repositoryName
     * @return void
     */
    public function push(bool $showOutput = true, string $repositoryName = 'origin')
    {
        if (!$this->hasSelectedChanges()) {
            return;
        }

        GitPush::make([
            'repository' => $this->repository(),
            'options' => [$repositoryName],
            'showCommand' => false,
            'showOutput' => $showOutput,
        ])->run();
    }

    /**
     * Make a new instance of this class based on parameters
     *
     * @param GitRepository $repository
     * @return static
     */
    public static function make(GitRepository $repository = null)
    {
        $r = new static();

        if ($repository != null) {
            $r->repository = $repository;
        }

        return $r;
    }
}
