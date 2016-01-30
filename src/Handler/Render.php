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
use Zend\Diactoros\Stream;

/**
 * Render HTML page maintenance handler.
 */
class Render implements HandlerInterface
{
    /**
     * Known handled content types.
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
    ];

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Watcher $watcher)
    {
        $content = '';

        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $content = $this->renderJson($watcher);
                break;
            case 'text/xml':
            case 'application/xml':
                $content = $this->renderXml($watcher);
                break;
            case 'text/html':
                $content = $this->renderHtml($watcher);
                break;
        }

        $body = new Stream('php://temp', 'r+');
        $body->write($content);

        if ($watcher instanceof ScheduledWatcher) {
            $response = $response->withHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'));
        } else {
            $response = $response->withHeader('Cache-Control', 'max-age=0')
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');
        }

        return $response->withStatus(503)
            ->withHeader('Content-Type', $contentType)
            ->withBody($body);
    }

    /**
     * Render HTML maintenance message.
     *
     * @param \Janitor\Watcher $watcher
     *
     * @return string
     */
    protected function renderHtml(Watcher $watcher)
    {
        $title = 'Maintenance';
        $message = $this->getMaintenanceMessage($watcher);

        return sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>",
            $title,
            $title,
            $message
        );
    }

    /**
     * Render JSON maintenance message.
     *
     * @param \Janitor\Watcher $watcher
     *
     * @return string
     */
    protected function renderJson(Watcher $watcher)
    {
        return json_encode(['message' => $this->getMaintenanceMessage($watcher)], JSON_PRETTY_PRINT);
    }

    /**
     * Render XML maintenance message.
     *
     * @param \Janitor\Watcher $watcher
     *
     * @return string
     */
    protected function renderXml(Watcher $watcher)
    {
        return sprintf(
            "<maintenance>\n  <message>%s</message>\n</maintenance>",
            $this->getMaintenanceMessage($watcher)
        );
    }

    /**
     * Retrieve custom maintenance message.
     *
     * @param \Janitor\Watcher $watcher
     *
     * @return string
     */
    protected function getMaintenanceMessage(Watcher $watcher)
    {
        $message = 'Maintenance mode is not active!';
        if ($watcher->isActive()) {
            $message = $watcher instanceof ScheduledWatcher
                ? 'Undergoing maintenance tasks until ' . $watcher->getEnd()->format('Y/m/d H:i:s')
                : 'Undergoing maintenance tasks';
        }

        return $message;
    }

    /**
     * Determine content type using Accept header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     */
    private function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
        if (count($selectedContentTypes)) {
            return $selectedContentTypes[0];
        }

        return 'text/html';
    }
}
