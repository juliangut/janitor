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
use Zend\Diactoros\Stream;

/**
 * HTML render maintenance handler.
 */
class Render implements HandlerInterface
{
    /**
     * Known handled content types.
     *
     * @var array
     */
    protected $knownContentTypes = [
        'text/html',
        'text/json',
        'application/json',
        'text/xml',
        'application/xml',
    ];

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, WatcherInterface $watcher)
    {
        $content = '';

        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'text/json':
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

        if ($watcher instanceof ScheduledWatcherInterface) {
            $response = $response
                ->withHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'))
                ->withHeader('Retry-After', $watcher->getEnd()->format('D, d M Y H:i:s e'));
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
     * @param WatcherInterface $watcher
     *
     * @return string
     */
    protected function renderHtml(WatcherInterface $watcher)
    {
        $title = 'Maintenance';
        $message = $this->getMaintenanceMessage($watcher);

        return sprintf(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">' .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{' .
            'display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $message
        );
    }

    /**
     * Render JSON maintenance message.
     *
     * @param WatcherInterface $watcher
     *
     * @return string
     */
    protected function renderJson(WatcherInterface $watcher)
    {
        return json_encode(['message' => $this->getMaintenanceMessage($watcher)]);
    }

    /**
     * Render XML maintenance message.
     *
     * @param WatcherInterface $watcher
     *
     * @return string
     */
    protected function renderXml(WatcherInterface $watcher)
    {
        return sprintf(
            '<maintenance><message>%s</message></maintenance>',
            $this->getMaintenanceMessage($watcher)
        );
    }

    /**
     * Retrieve custom maintenance message.
     *
     * @param WatcherInterface $watcher
     *
     * @return string
     */
    protected function getMaintenanceMessage(WatcherInterface $watcher)
    {
        $message = 'Maintenance mode is not active!';
        if ($watcher->isActive()) {
            $message = $watcher instanceof ScheduledWatcherInterface
                ? 'Undergoing maintenance tasks until ' . $watcher->getEnd()->format('Y/m/d H:i:s')
                : 'Undergoing maintenance tasks';
        }

        return $message;
    }

    /**
     * Determine content type using Accept header.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);

        if (count($selectedContentTypes)) {
            return current($selectedContentTypes);
        }

        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $this->knownContentTypes, true)) {
                return $mediaType;
            }
        }

        return $this->knownContentTypes[0];
    }
}
