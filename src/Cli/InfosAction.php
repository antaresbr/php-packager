<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;
use Antares\Support\Packager\Infos\PackageInfos;

class InfosAction extends BaseCliAction
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
    packager infos [ options ]

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

        $infos = new PackageInfos();
        $infos->show();
    }
}
