<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;
use Antares\Support\Git\GitCommand;
use Antares\Support\Git\GitRepository;
use Antares\Support\Git\GitTagList;
use Antares\Support\Packager\Infos\PackageInfos;

class TagBuildAction extends BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes = [
        'type' => [
            'labels' => ['major', 'release', 'minor'],
            'help' => 'The build type',
            'required' => true,
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
        $types = implode(' | ', $this->params->getPrototype('type')['labels']);
        return "
Usage:
    packager tag build < type | --help >

Where:
    type 
        {$this->params->help(['type'], false, '')} [ {$types} ]

{$this->params->help(['help'])}

";
    }

    /**
     * Working repository
     *
     * @var \Antares\Support\Git\GitRepository
     */
    private $repository;

    /**
     * Private property repository accessor
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

        $tagList = GitTagList::make([
            'repository' => $this->repository(),
            'showCommand' => false,
            'showOutput' => false,
        ]);
        $localTags = $tagList->get();
        $remoteTags = $tagList->get(true);

        echo "localTags:\n";
        print_r($localTags);

        echo "remoteTags:\n";
        print_r($remoteTags);

        if ($infos->version->toString() == '0.0.0' or (in_array($infos->version->toString(), $localTags) and in_array($infos->version->toString(), $remoteTags))) {
            $infos->version->add($this->params->type);
            $infos->save();
            echo "Package new version: {$infos->version->toString()}\n";
        }

        if (!in_array($infos->version->toString(), $localTags)) {
            GitCommand::make([
                'repository' => $this->repository(),
                'command' => 'tag',
                'options' => [
                    '--annotate',
                    ['--message' => ucfirst($this->params->type) . ' version ' . $infos->version->toString()],
                    $infos->version->toString(),
                ],
                'showCommand' => false,
                'showOutput' => false,
            ])->run();
        }

        if (!in_array($infos->version->toString(), $remoteTags)) {
            echo "TODO : git push\n";
        }

        echo "\n";
    }
}
