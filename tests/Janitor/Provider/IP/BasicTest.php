<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Provider\Ip;

use Janitor\Provider\IP\Basic;

/**
 * @covers \Janitor\Provider\IP\Basic
 */
class BasicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Janitor\Provider\IP\Basic::getIPAddress
     */
    public function testEmptyIP()
    {
        $this->assertEmpty((new Basic)->getIPAddress());
    }

    /**
     * @covers \Janitor\Provider\IP\Basic::getIPAddress
     */
    public function testByRemoteAddr()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $this->assertNotFalse(filter_var((new Basic)->getIPAddress(), FILTER_SANITIZE_EMAIL));
    }

    /**
     * @covers \Janitor\Provider\IP\Basic::getIPAddress
     * @covers \Janitor\Provider\IP\Basic::getIpFromProxy
     */
    public function testByProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';

        $this->assertNotFalse(filter_var((new Basic)->getIPAddress(), FILTER_SANITIZE_EMAIL));
    }

    /**
     * @covers \Janitor\Provider\IP\Basic::getIPAddress
     */
    public function testByHttpClientIP()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

        $this->assertNotFalse(filter_var((new Basic)->getIPAddress(), FILTER_SANITIZE_EMAIL));
    }
}
