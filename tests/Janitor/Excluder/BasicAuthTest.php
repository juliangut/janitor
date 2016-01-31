<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\BasicAuth;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @covers \Janitor\Excluder\BasicAuth
 */
class BasicAuthTest extends \PHPUnit_Framework_TestCase
{
    protected $excludedUsers = [
        'root' => 'secret',
        'guest' => null,
    ];

    /**
     * @covers \Janitor\Excluder\BasicAuth::addUser
     * @covers \Janitor\Excluder\BasicAuth::isExcluded
     * @covers \Janitor\Excluder\BasicAuth::getAuth
     *
     * @dataProvider usersProvider
     */
    public function testIsExcluded($username, $password)
    {
        $authString = $username . ($password === null ? '' : ':' . $password);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Authorization', 'Basic ' . base64_encode($authString));

        $excluder = new BasicAuth();
        $excluder->addUser($username, $password);

        $this->assertTrue($excluder->isExcluded($request));
    }

    /**
     * Users provider.
     *
     * @return array
     */
    public function usersProvider()
    {
        return [
            ['root', 'secret'],
            ['guest', null],
        ];
    }

    /**
     * @covers \Janitor\Excluder\BasicAuth::__construct
     * @covers \Janitor\Excluder\BasicAuth::isExcluded
     * @covers \Janitor\Excluder\BasicAuth::getAuth
     */
    public function testIsNotExcluded()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new BasicAuth(['root' => 'secret']);

        $this->assertFalse($excluder->isExcluded($request));
    }
}
