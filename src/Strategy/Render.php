<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Strategy;

use Janitor\Strategy as StrategyInterface;
use Janitor\Watcher;
use Janitor\ScheduledWatcher;

/**
 * Render HTML page maintenance strategy.
 */
class Render implements StrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(Watcher $watcher)
    {
        header('Content-Type: text/html; charset=utf-8');

        if ($watcher instanceof ScheduledWatcher) {
            header('Expires: ' . $watcher->getEnd()->format('D, d M Y H:i:s e'));
        } else {
            header('Cache-Control: max-age=0');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }

        $message = 'Maintenance mode is not active';
        if ($watcher->isActive()) {
            $message = $watcher instanceof ScheduledWatcher
            ? 'Undergoing maintenance tasks until ' . $watcher->getEnd()->format('Y/m/d H:i:s')
            : 'Undergoing maintenance tasks';
        }

        http_response_code(503);

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

        echo $content;
    }
}
