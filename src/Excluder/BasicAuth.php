<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Excluder;

use Janitor\Excluder as ExcluderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance excluder by Basic Authorization
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
     * @param array $users
     */
    public function __construct(array $users = [])
    {
        foreach ($users as $username => $password) {
            $this->addUser($username, $password);
        }
    }

    /**
     * Add user.
     *
     * @param string      $username
     * @param string|null $password
     *
     * @return $this
     */
    public function addUser($username, $password = null)
    {
        $this->users[trim($username)] = $password;

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
