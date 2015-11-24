<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Strategy;

use Janitor\Strategy\Render;

/**
 * @covers \Janitor\Strategy\Render
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Janitor\Strategy\Render::handle
     * @runInSeparateProcess
     */
    public function testRenderingNotActive()
    {
        $strategy = new Render;

        $expected = 'Maintenance mode is not active';

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(false));

        ob_start();
        $strategy->handle($watcher);
        $return = ob_get_clean();

        $this->assertNotFalse(strpos($return, $expected));
    }

    /**
     * @covers \Janitor\Strategy\Render::handle
     * @runInSeparateProcess
     */
    public function testRenderingActive()
    {
        $strategy = new Render;

        $expected = 'Undergoing maintenance tasks';

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(true));

        ob_start();
        $strategy->handle($watcher);
        $return = ob_get_clean();

        $this->assertNotFalse(strpos($return, $expected));
    }

    /**
     * @covers \Janitor\Strategy\Render::handle
     * @runInSeparateProcess
     */
    public function testRenderingScheduled()
    {
        $strategy = new Render;

        $expected = 'Undergoing maintenance tasks until';

        $watcher = $this->getMock('Janitor\\ScheduledWatcher');
        $watcher->expects($this->any())->method('getEnd')->will($this->returnValue(new \DateTime));
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(true));

        ob_start();
        $strategy->handle($watcher);
        $return = ob_get_clean();

        $this->assertNotFalse(strpos($return, $expected));
    }
}
