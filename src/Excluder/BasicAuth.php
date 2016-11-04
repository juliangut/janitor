<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Excluder;

use Janitor\Excluder as ExcluderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance excluder by Basic Authorization.
 */
class BasicAuth implements ExcluderInterface
{
    /**
     * List of user/password to be excluded.
     *
     * @var array
     */
    protected $users = [];

    /**
     * @param string|array|null $users
     * @param mixed|null        $password
     */
    public function __construct($users = null, $password = null)
    {
        if (!is_array($users)) {
            $users = [$users => $password];
        }

        foreach ($users as $userName => $userPassword) {
            $this->addUser($userName, $userPassword);
        }
    }

    /**
     * Add user.
     *
     * @param string     $userName
     * @param mixed|null $password
     *
     * @return $this
     */
    public function addUser($userName, $password = null)
    {
        if (trim($userName) !== '') {
            $this->users[trim($userName)] = $password;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        $authData = $this->getAuth($request);

        foreach ($this->users as $username => $password) {
            if ($authData['username'] === $username && $authData['password'] === $password) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve request authentication information.
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    protected function getAuth(ServerRequestInterface $request)
    {
        $authData = [
            'username' => null,
            'password' => null,
        ];

        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/^Basic /', $authHeader)) {
            $auth = explode(':', base64_decode(substr($authHeader, 6)), 2);

            $authData['username'] = $auth[0];
            $authData['password'] = isset($auth[1]) ? $auth[1] : null;
        }

        return $authData;
    }
}
