<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;
use Antares\Support\Git\GitChangesHandler;
use Antares\Support\Git\GitRepository;
use Antares\Support\Git\GitTag;
use Antares\Support\Packager\Infos\PackageInfos;

class BuildAction extends BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes = [
        'type' => [
            'labels' => ['major', 'release', 'minor'],
            'helpTitle' => '<{{option}}>',
            'help' => 'The build type : < {{labels:pipe}} >',
            'required' => true,
        ],
        'force' => [
            'labels' => ['--force'],
            'help' => 'Force build even if there is nothing to commit',
            'default' => false,
        ],
        'help' => [
            'labels' => ['--help'],
            'help' => 'Show this help message',
        ],
    ];

    /**
     * Get help message
     *
     * @return string
     */
    protected function help()
    {
        return "
Usage:
    packager build <options>

Where options:
{$this->params->help()}

";
    }

    /**
     * Working repository
     *
     * @var \Antares\Support\Git\GitRepository
     */
    private $repository;

    /**
     * Acessor for repository property
     *
     * @return \Antares\Support\Git\GitRepository
     */
    private function repository()
    {
        if ($this->repository == null) {
            $this->repository = new GitRepository(getcwd());
        }
        return $this->repository;
    }

    /**
     * Private GitTag object instance
     *
     * @var \Antares\Support\Git\GitTag
     */
    private $gitTag;

    /**
     * Acessor for gitTag property
     *
     * @return \Antares\Support\Git\GitTag
     */
    private function gitTag()
    {
        if ($this->gitTag == null) {
            $this->gitTag = GitTag::make([
                'repository' => $this->repository(),
                'showCommand' => false,
                'showOutput' => false,
            ]);
        }
        return $this->gitTag;
    }

    /**
     * Private GitChangesHandler object instance
     *
     * @var \Antares\Support\Git\GitChangesHandler
     */
    private $changes;

    /**
     * Acessor for changes property
     *
     * @return \Antares\Support\Git\GitChangesHandler
     */
    private function changes()
    {
        if ($this->changes == null) {
            $this->changes = GitChangesHandler::make($this->repository());
        }
        return $this->changes;
    }

    /**
     * @see BaseCliAction::run()
     */
    public function run($params)
    {
        $this->params->parse($params);

        if ($this->params->help) {
            $this->showHelp();
        }

        $infos = new PackageInfos();
        $infos->show();

        echo "get changes\n";

        $this->changes()->getLocalChanges();
        if ($this->changes()->hasChanges()) {
            $this->changes()->doSelection();
        }

        if (!$this->params->force and !$this->changes()->hasSelectedChanges()) {
            $this->showAndExit('No changes found or selected.');
        }

        if ($this->changes()->hasSelectedChanges()) {
            $this->changes()->inputCommitMessage();
            if (!$this->changes()->hasCommitMessage()) {
                $this->showError('No commit message, aborting.');
            }
        }

        echo "fetch tags from remote\n";
        $this->gitTag()->fetchFromRemote();

        echo "get local tag list\n";
        $localTags = $this->gitTag()->getLocalList();
        $lastLocalTag = empty($localTags) ? '0.0.0' : $localTags[array_key_last($localTags)];
        if ($infos->version->isLessThan($lastLocalTag)) {
            $infos->version->set($lastLocalTag);
        }

        echo "get remote tag list\n";
        $remoteTags = $this->gitTag()->getRemoteList();

        if ($infos->version->toString() == '0.0.0' or (in_array($infos->version->toString(), $localTags) and in_array($infos->version->toString(), $remoteTags))) {
            $infos->version->add($this->params->type);
            if (in_array($infos->version->toString(), $localTags)) {
                $infos->version->set($lastLocalTag);
                $infos->version->add($this->params->type);
            }
            echo "package new version: {$infos->version->toString()}\n";
            $infos->save();
            if ($this->changes()->hasChange($infos->getFileName())) {
                $this->changes()->selectChange($infos->getFileName());
            } else {
                $this->changes()->addChange($infos->getFileName(), true);
            }
            if (!$this->changes()->hasCommitMessage()) {
                $this->changes()->setCommitMessage(['Update package infos file.']);
            }
        }

        if ($this->changes()->hasSelectedChanges()) {
            echo "stage changes\n";
            $this->changes()->stageSelectedChanges();
            echo "commit changes\n";
            $this->changes()->commit();
        }

        if ($this->changes()->hasCommitToPush()) {
            echo "push changes\n";
            $this->changes()->push();
        }

        if (!in_array($infos->version->toString(), $localTags)) {
            echo "create tag {$infos->version->toString()}\n";
            $this->gitTag()->add($infos->version->toString(), ucfirst($this->params->type) . ' version ' . $infos->version->toString());
        }

        if (!in_array($infos->version->toString(), $remoteTags)) {
            echo "push tags\n";
            $this->gitTag()->showOutput = true;
            $this->gitTag()->push($infos->version->toString());
        }

        echo "\n";
    }
}
