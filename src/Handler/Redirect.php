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

use Janitor\Watcher\ScheduledWatcherInterface;
use Janitor\Watcher\WatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Redirect maintenance handler.
 */
class Redirect implements HandlerInterface
{
    private $location;

    /**
     * Redirect constructor.
     *
     * @param string $location
     */
    public function __construct($location)
    {
        $this->location = (string) $location;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, WatcherInterface $watcher)
    {
        if ($watcher instanceof ScheduledWatcherInterface) {
            $response = $response->withHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'));
        } else {
            $response = $response->withHeader('Cache-Control', 'max-age=0')
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');
        }

        $location = $this->location;
        if (!preg_match('/^https?:\/\//', $location)) {
            $location = (string) $request->getUri()->withPath($location);
        }

        return $response->withStatus(302)
            ->withHeader('Location', $location);
    }
}
