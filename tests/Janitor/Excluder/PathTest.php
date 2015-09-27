<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\Path;

/**
 * @covers Janitor\Excluder\Path
 */
class PathTest extends \PHPUnit_Framework_TestCase
{
    protected $excludedPaths = array(
        '/user',
        '/blog/post',
    );

    /**
     * @covers Janitor\Excluder\Path::__construct
     * @covers Janitor\Excluder\Path::addPath
     */
    public function testCreationInvalidIP()
    {
        new Path;
    }

    /**
     * @covers Janitor\Excluder\Path::__construct
     * @covers Janitor\Excluder\Path::addPath
     * @covers Janitor\Excluder\Path::isExcluded
     */
    public function testIsExcluded()
    {
        $pathProvider = $this->getMock('Janitor\\Provider\\Path\\Basic');
        $pathProvider->expects($this->once())->method('getPath')->will($this->returnValue('/user'));

        $exclusion = new Path($this->excludedPaths, $pathProvider);

        $this->assertTrue($exclusion->isExcluded());
    }

    /**
     * @covers Janitor\Excluder\Path::isExcluded
     */
    public function testIsNotExcluded()
    {
        $pathProvider = $this->getMock('Janitor\\Provider\\Path\\Basic');
        $pathProvider->expects($this->once())->method('getPath')->will($this->returnValue('/home'));

        $exclusion = new Path($this->excludedPaths, $pathProvider);

        $this->assertFalse($exclusion->isExcluded());
    }
}
