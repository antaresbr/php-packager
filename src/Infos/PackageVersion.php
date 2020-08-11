<?php

namespace Antares\Support\Packager\Infos;

class PackageVersion
{
    /**
     * Major version number
     *
     * @var int
     */
    public $major;

    /**
     * Release version number
     *
     * @var int
     */
    public $release;

    /**
     * Minor version number
     *
     * @var int
     */
    public $minor;

    /**
     * Class construtor
     *
     * @param int $major
     * @param int $release
     * @param int $minor
     */
    public function __construct($major, $release, $minor)
    {
        $this->major = $major;
        $this->release = $release;
        $this->minor = $minor;
    }

    /**
     * Add major version
     *
     * @return static
     */
    public function addMajor()
    {
        $this->major++;
        $this->release = 0;
        $this->minor = 0;
        return $this;
    }

    /**
     * Add release version
     *
     * @return static
     */
    public function addRelease()
    {
        $this->release++;
        $this->minor = 0;
        return $this;
    }

    /**
     * Add minor version
     *
     * @return static
     */
    public function addMinor()
    {
        $this->minor++;
        return $this;
    }

    /**
     * Add version
     *
     * @param string $type
     * @return static
     */
    public function add(string $type)
    {
        $type = trim($type);
        if ($type != '') {
            $type = 'add' . ucfirst(strtolower($type));
            $this->{$type}();
        }
        return $this;
    }

    /**
     * Get array representation of this object
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'major' => $this->major,
            'release' => $this->release,
            'minor' => $this->minor,
        ];
    }

    /**
     * Get string representation of this object
     *
     * @return string
     */
    public function toString()
    {
        return "{$this->major}.{$this->release}.{$this->minor}";
    }
}
