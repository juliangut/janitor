<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Strategy;

use Janitor\Strategy\Redirect;

/**
 * @covers Janitor\Strategy\Redirect
 */
class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Janitor\Strategy\Redirect::handle
     * @runInSeparateProcess
     */
    public function testRendering()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $strategy = new Redirect('http://example.com');

        ob_start();
        $strategy->handle($watcher);
        $return = ob_get_clean();
    }
}
