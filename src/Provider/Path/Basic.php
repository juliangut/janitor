<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Provider\Path;

use Janitor\Provider\Path as PathInterface;

/**
 * Basic Path provider.
 */
class Basic implements PathInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $scriptName = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
        $scriptDir  = dirname($scriptName);

        $basePath = '';
        $currentPath = $requestUri;

        if (stripos($requestUri, $scriptName) === 0) {
            $basePath = $scriptName;
        } elseif ($scriptDir !== '/' && stripos($requestUri, $scriptDir) === 0) {
            $basePath = $scriptDir;
        }

        if ($basePath) {
            $currentPath = ltrim(substr($requestUri, strlen($basePath)), '/');
        }

        return trim($currentPath, '/');
    }
}
