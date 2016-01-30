<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Handler;

use Janitor\Handler as HandlerInterface;
use Janitor\Watcher;
use Janitor\ScheduledWatcher;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Redirect maintenance handler.
 */
class Redirect implements HandlerInterface
{
    private $location;

    /**
     * @param string $location
     */
    public function __construct($location)
    {
        $this->location = (string) $location;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Watcher $watcher)
    {
        if ($watcher instanceof ScheduledWatcher) {
            $response = $response->withHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'));
        } else {
            $response = $response->withHeader('Cache-Control', 'max-age=0')
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');
        }

        return $response->withStatus(302)
            ->withHeader('Location', $this->location);
    }
}
