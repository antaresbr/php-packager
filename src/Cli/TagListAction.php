<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;
use Antares\Support\Git\GitTagList;

class TagListAction extends BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes = [
        'remote' => [
            'labels' => ['--remote'],
            'help' => 'Flag to list remote tags instead of local tags',
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
    packager tag list [ options ]

Where options:
{$this->params->help()}

";
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

        echo "\n";
        echo "Tag list:\n";

        GitTagList::make([
            'showCommand' => false,
            'showOutput' => true,
        ])->get($this->params->remote);

        echo "\n";
    }
}
