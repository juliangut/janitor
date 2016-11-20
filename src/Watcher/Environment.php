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

/**
 * Environment variable maintenance status watcher.
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
     * Environment constructor.
     *
     * @param array|string $vars
     * @param mixed        $value
     */
    public function __construct($vars, $value = null)
    {
        if ($vars !== null && !is_array($vars)) {
            $vars = [$vars => $value];
        }

        if (is_array($vars)) {
            foreach ($vars as $varName => $varValue) {
                $this->addVariable($varName, $varValue);
            }
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
        if (!count($this->vars)) {
            throw new \RuntimeException('At least one environment variable must be defined');
        }

        foreach ($this->vars as $variable => $value) {
            if ($value === null && $this->isEnvVariableDefined($variable)) {
                return true;
            } elseif ($value !== null && $this->getEnvVariableValue($variable) === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test for environment variable.
     *
     * @param string $var
     *
     * @return bool
     */
    protected function isEnvVariableDefined($variable)
    {
        return getenv($variable) !== false;
    }

    /**
     * Get environment variable value.
     *
     * @param string $variable
     *
     * @return mixed
     */
    protected function getEnvVariableValue($variable)
    {
        $value = getenv($variable);

        if ($value === false) {
            return;
        }

        switch (strtolower($value)) {
            case 'true':
                return true;

            case 'false':
                return false;
        }

        if (preg_match('/^[\'"](.*)[\'"]$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}
