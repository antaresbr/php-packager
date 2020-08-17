<?php

namespace Antares\Support\Packager\Cli;

use Antares\Support\BaseCli\BaseCliAction;

class TagAction extends BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes = [
        'action' => [
            'labels' => ['list', 'fetch'],
            'helpTitle' => '{{option}}',
            'help' => [
                'Action to be performed [ {{labels:pipe}} ]',
                'list  : Show tag list',
                'fetch : Fetch tags from remote',
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
        $actionLabels = implode(' | ', $this->params->getPrototype('action')['labels']);
        return "
Usage:
    packager tag < action | --help >

Where:
{$this->params->help()}

";
    }

    /**
     * @see BaseCliAction::run()
     */
    public function run($params)
    {
        $this->params->parse($params, true);

        if ($this->params->help) {
            $this->showHelp();
        }

        if (empty($this->params->action)) {
            $this->showError('No action supplied.');
        }

        $actionClass = __NAMESPACE__ . '\\Tag' . ucfirst($this->params->action) . 'Action';
        $actionClass::exec($params);
    }
}
