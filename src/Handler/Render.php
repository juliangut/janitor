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
 * Render HTML page maintenance handler.
 */
class Render implements HandlerInterface
{
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

        $message = 'Maintenance mode is not active';
        if ($watcher->isActive()) {
            $message = $watcher instanceof ScheduledWatcher
            ? 'Undergoing maintenance tasks until ' . $watcher->getEnd()->format('Y/m/d H:i:s')
            : 'Undergoing maintenance tasks';
        }

        $content = <<<EOF
<html>
<head>
<title>Maintenance</title>
<style>
    body{margin:0;padding:30px;font:20px Helvetica,Arial,Verdana,sans-serif;text-align:center;}
    h1{font-weight:normal;}
</style>
</head>
<body>
    <h1>Under maintenance</h1>
    {$message}
</body>
</html>
EOF;

        $response->withStatus(503)
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->getBody()->write($content);

        return $response;
    }
}
