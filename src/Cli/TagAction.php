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
            'labels' => ['list', 'build'],
            'help' => [
                'list  : Show tag list',
                'build : Create and commit a new tag version',
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
    action  Action to be performed [ {$actionLabels} ]
{$this->params->help(['action'], false, str_pad('', 12))}

{$this->params->help(['help'])}

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
