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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Basic HTTP authorization maintenance excluder.
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
     * BasicAuth constructor.
     *
     * @param string|array $users
     * @param mixed        $password
     */
    public function __construct($users = null, $password = null)
    {
        if ($users !== null && !is_array($users)) {
            $users = [$users => $password];
        }

        if (is_array($users)) {
            foreach ($users as $userName => $userPassword) {
                $this->addUser($userName, $userPassword);
            }
        }
    }

    /**
     * Add user.
     *
     * @param string $userName
     * @param mixed  $password
     *
     * @return $this
     */
    public function addUser($userName, $password = null)
    {
        if (is_string($userName) && trim($userName) !== '') {
            $this->users[trim($userName)] = $password;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        if (!count($this->users)) {
            throw new \RuntimeException('No users defined in basic authorization excluder');
        }

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
        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/^Basic /', $authHeader)) {
            $auth = explode(':', base64_decode(substr($authHeader, 6)), 2);

            return [
                'username' => $auth[0],
                'password' => isset($auth[1]) ? $auth[1] : null,
            ];
        }
    }
}
