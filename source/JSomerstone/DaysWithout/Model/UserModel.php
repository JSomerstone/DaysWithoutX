<?php
namespace JSomerstone\DaysWithout\Model;

use JSomerstone\DaysWithout\Lib\StringFormatter;

/**
 * Class UserModel
 * @package JSomerstone\DaysWithout\Model
 */
class UserModel implements ModelInterface
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
     * @param UserModel $other
     * @return bool
     */
    public function isSameAs(UserModel $other)
    {
        return ($this->nick === $other->getNick());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
    public function __toString()
    {
        return $this->nick;
    }

}
