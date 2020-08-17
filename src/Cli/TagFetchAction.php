<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;
use Antares\Support\Git\GitTag;

class TagFetchAction extends BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes = [
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
    packager tag fetch [ options ]

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
        echo "Tag fetch:\n";

        GitTag::make([
            'showCommand' => false,
            'showOutput' => true,
        ])->fetchFromRemote();

        echo "\n";
    }
}
