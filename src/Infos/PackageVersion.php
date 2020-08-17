<?php

namespace Antares\Support\Packager\Infos;

use Antares\Support\Packager\Exceptions\PackagerException;

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
     * Set version
     *
     * @param mixed $value
     * @return void
     */
    public function set($value)
    {
        if ($value instanceof static) {
            $this->major = $value->major;
            $this->release = $value->release;
            $this->minor = $value->minor;
        } elseif (is_array($value)) {
            $major = isset($value[0]) ? $value[0] : (isset($value['major']) ? $value['major'] : 0);
            $release = isset($value[1]) ? $value[1] : (isset($value['release']) ? $value['release'] : 0);
            $minor = isset($value[2]) ? $value[2] : (isset($value['minor']) ? $value['minor'] : 0);
            if (
                filter_var($major, FILTER_VALIDATE_INT) === false or
                filter_var($release, FILTER_VALIDATE_INT) === false or
                filter_var($minor, FILTER_VALIDATE_INT) === false
            ) {
                throw PackagerException::forInvalidVersionValue($value);
            }
            $this->major = (int)$major;
            $this->release = (int)$release;
            $this->minor = (int)$minor;
        } elseif (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            $this->major = (int)$value;
            $this->release = 0;
            $this->minor = 0;
        } elseif (is_string($value)) {
            $this->set(explode('.', $value));
        } else {
            throw PackagerException::forInvalidVersionValue($value);
        }
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

    /**
     * Check if this version is less than a supplied versrion
     *
     * @param mixed $value
     * @return bool
     */
    public function isLessThan($value)
    {
        if (!($value instanceof static)) {
            $target = new static(0, 0, 0);
            $target->set($value);
            $value = $target;
        }

        $thisFormatted = str_pad($this->major, 10, ' ', STR_PAD_LEFT) . str_pad($this->release, 10, ' ', STR_PAD_LEFT) . str_pad($this->minor, 10, ' ', STR_PAD_LEFT);
        $valueFormatted = str_pad($value->major, 10, ' ', STR_PAD_LEFT) . str_pad($value->release, 10, ' ', STR_PAD_LEFT) . str_pad($value->minor, 10, ' ', STR_PAD_LEFT);

        return ($thisFormatted < $valueFormatted);
    }
}
