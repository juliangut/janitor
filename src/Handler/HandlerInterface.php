<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Handler;

use Janitor\Watcher\WatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance handler interface.
 */
interface HandlerInterface
{
    /**
     * Run handler.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param WatcherInterface       $watcher
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, WatcherInterface $watcher);
}
