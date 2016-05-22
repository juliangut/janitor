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
 * Class BasicAuthTest
 */
class BasicAuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $excludedUsers = [
        'root' => 'secret',
        'guest' => null,
    ];

    /**
     * @dataProvider usersProvider
     */
    public function testIsExcluded($username, $password)
    {
        $authString = $username . ($password === null ? '' : ':' . $password);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Authorization', 'Basic ' . base64_encode($authString));

        $excluder = new BasicAuth;
        $excluder->addUser($username, $password);

        self::assertTrue($excluder->isExcluded($request));
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

    public function testIsNotExcluded()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new BasicAuth(['root' => 'secret']);

        self::assertFalse($excluder->isExcluded($request));
    }
}
