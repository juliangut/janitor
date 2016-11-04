<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Watcher;

use Janitor\Watcher as WatcherInterface;

/**
 * Environment variable check for maintenance status watcher.
 */
class Environment implements WatcherInterface
{
    /**
     * Environment variable.
     *
     * @var array
     */
    protected $vars;

    /**
     * @param array|string $vars
     * @param mixed|null   $value
     */
    public function __construct($vars, $value = null)
    {
        if (!is_array($vars)) {
            $vars = [$vars => $value];
        }

        foreach ($vars as $varName => $varValue) {
            $this->addVariable($varName, $varValue);
        }
    }

    /**
     * Set environment variable.
     *
     * @param string $var
     * @param mixed  $value
     *
     * @return $this
     */
    public function addVariable($var, $value = null)
    {
        if (trim($var) !== '') {
            $this->vars[trim($var)] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        foreach ($this->vars as $var => $value) {
            if ($value === null && getenv($var) !== false) {
                return true;
            } elseif ($value !== null && getenv($var) === $value) {
                return true;
            }
        }

        return false;
    }
}
