<?php

namespace Antares\Support\Packager\Infos;

use Antares\Support\Packager\Exceptions\PackagerException;
use Antares\Foundation\Str;

class PackageInfos
{
    /**
     * Infos file name
     *
     * @var string
     */
    protected $fileName = 'infos.json';

    /**
     * Infos file base dir
     *
     * @var string
     */
    protected $baseDir;

    /**
     * Package name
     *
     * @var string
     */
    public $name;

    /**
     * Package version
     *
     * @var PackageVersion
     */
    public $version;

    /**
     * Class constructor
     *
     * @param string $baseDir
     */
    public function __construct(string $baseDir = 'support')
    {
        $this->baseDir = $baseDir;

        $this->load();
    }

    /**
     * Get full infos file name
     *
     * @return string
     */
    public function getFileName()
    {
        return Str::join(DIRECTORY_SEPARATOR, $this->baseDir, $this->fileName);
    }

    /**
     * Load infos content from file
     *
     * @return void
     */
    protected function load()
    {
        $fileName = $this->getFileName();
        if (!file_exists($fileName)) {
            throw PackagerException::forInfosNotFound($fileName);
        }

        $infos = json_decode(file_get_contents($fileName));

        $this->name = $infos->name;
        $this->version = new PackageVersion($infos->version->major, $infos->version->release, $infos->version->minor);
    }

    /**
     * Reload infos content
     *
     * @return void
     */
    public function reload()
    {
        $this->load();
    }

    /**
     * Show package infos
     *
     * @return void
     */
    public function show()
    {
        echo "\n";
        echo "name   : {$this->name}\n";
        echo "version: {$this->version->toString()}\n";
        echo "\n";
    }

    /**
     * Get array representation of this object
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'version' => $this->version->toArray(),
        ];
    }

    /**
     * Save infos content to file
     *
     * @return void
     */
    public function save()
    {
        return file_put_contents($this->getFileName(), json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
