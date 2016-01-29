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
            $response->setHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'));
        } else {
            $response->setHeader('Cache-Control', 'max-age=0')
                ->setHeader('Cache-Control', 'no-cache, must-revalidate')
                ->setHeader('Pragma', 'no-cache');
        }

        return $response->setStatus(302)
            ->setHeader('Location', $this->location);
    }
}
