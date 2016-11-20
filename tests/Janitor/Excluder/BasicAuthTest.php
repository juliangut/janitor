<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\BasicAuth;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Basic HTTP authorization maintenance excluder tests.
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
     * @expectedException \RuntimeException
     */
    public function testNoUser()
    {
        $excluder = new BasicAuth('');

        $excluder->isExcluded(ServerRequestFactory::fromGlobals());
    }

    /**
     * @dataProvider usersProvider
     */
    public function testIsExcluded($username, $password)
    {
        $authString = $username . ($password === null ? '' : ':' . $password);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Authorization', 'Basic ' . base64_encode($authString));

        $excluder = new BasicAuth('');
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
