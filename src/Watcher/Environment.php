<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Watcher;

use Janitor\Watcher as WatcherInterface;

/**
 * Environment variable check for maintenance status provider.
 */
class Environment implements WatcherInterface
{
    /**
     * Environment variable.
     *
     * @var string
     */
    protected $var;

    /**
     * Environment variable value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * @param string $var
     * @param mixed $value
     */
    public function __construct($var, $value = null)
    {
        $this->setVar($var);
        $this->setValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        if (getenv($this->var) === false) {
            return false;
        }

        return $this->value === null ? true : getenv($this->var) === $this->value;
    }

    /**
     * Set environment variable name.
     *
     * @param string $var
     */
    public function setVar($var)
    {
        $this->var = (string) $var;

        return $this;
    }

    /**
     * Get environment variable name.
     *
     * @return string
     */
    public function getVar()
    {
        return $this->var;
    }

    /**
     * Set environment variable value.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get environment variable value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
