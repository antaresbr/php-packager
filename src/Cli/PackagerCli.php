<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;

class PackagerCli extends BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes = [
        'action' => [
            'labels' => ['build', 'infos', 'tag'],
            'helpTitle' => '{{option}}',
            'help' => [
                'Action to be performed [ {{labels:pipe}} ]',
                'build : Build a package version',
                'infos : Show package infos',
                'tag   : Tag actions',
            ],
            'required' => true,
            'stopHere' => true,
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
    packager < action | --help >

Where:
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

        if (empty($this->params->action)) {
            $this->showError('No action supplied.');
        }

        $actionClass = __NAMESPACE__ . '\\' . ucfirst($this->params->action) . 'Action';
        $actionClass::exec($params);
    }
}
