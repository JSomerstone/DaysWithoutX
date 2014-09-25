<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserModel
 * @package JSomerstone\DaysWithoutBundle\Model
 */
class UserModel implements ModelInterface, UserInterface
{
    const ROLE_USER = 'user';

    private $id;

    private $nick;

    private $password;

    private $counters = array();

    /**
     * @param string $nick
     * @param string $password optional
     */
    public function __construct($nick = null, $password = null)
    {
        $this->nick = $nick;
        $this->id = StringFormatter::getUrlSafe($nick);
        $this->password = is_null($password)
            ? null
            : $this->hashPassword($password);
    }

    /**
     * @param $nick
     */
    public function setNick($nick)
    {
        $this->id = StringFormatter::getUrlSafe($nick);
        $this->nick = $nick;
    }

    /**
     * @return string|null
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CounterModel $counter
     */
    public function addCounter(CounterModel $counter)
    {
        $this->counters[$counter->getName()] = $counter;
    }

    /**
     * @param $name
     * @return CounterModel|null
     */
    public function getCounter($name)
    {
        return isset($this->counters[$name]) ? $this->counters[$name] : null;
    }

    /**
     * @return array
     */
    public function getCounters()
    {
        return $this->counters;
    }

    /**
     * @param $password
     * @throws \LogicException if "nick" is not set
     */
    public function setPassword($password)
    {
        if ( ! isset($this->nick))
        {
            throw new \LogicException('Users nick must be set before password');
        }
        $this->password = self::hashPassword($password, $this->nick);
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $plainTextPassword
     * @return bool
     */
    public function authenticate($plainTextPassword)
    {
        return $this->password
            === self::hashPassword($plainTextPassword, $this->nick);
    }

    /**
     * @param object $userAsJson
     * @return UserModel
     */
    public function fromJsonObject($userAsJson)
    {
        if ($userAsJson->nick)
            $this->nick = $userAsJson->nick;
        if ($userAsJson->id)
            $this->id = $userAsJson->id;
        if ($userAsJson->password)
            $this->password = $userAsJson->password;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'nick' => $this->nick,
            'id' => $this->id,
            'password' => $this->password
        );
    }

    /**
     * @param array $user
     * @return UserModel
     */
    public function fromArray(array $user)
    {
        if (isset($user['id']))
            $this->id = $user['id'];
        if (isset($user['nick']))
            $this->nick = $user['nick'];
        if (isset($user['password']))
            $this->password = $user['password'];

        return $this;
    }

    private function hashPassword($password)
    {
        return hash('sha256', $this->getSalt() . "$password");
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return hash('sha256', $this->nick);
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->getNick();
    }

    /**
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return array(self::ROLE_USER);
    }

    /**
     * Removes sensitive data from object
     */
    public function eraseCredentials()
    {
        $this->password = null;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return $this->nick;
    }

}
