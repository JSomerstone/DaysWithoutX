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

    private $email;

    /**
     * @param string $nick
     * @param string $email optional
     * @param string $password optional
     */
    public function __construct($nick = null, $email = null, $password = null)
    {
        $this->nick = $nick;
        $this->email = $email;
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
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
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
            'email' => $this->email,
            'id' => $this->id,
            'password' => $this->password,
        );
    }

    /**
     * @param array $user
     * @return UserModel
     */
    public function fromArray(array $user)
    {
        foreach(array('id', 'nick', 'email', 'password') as $property)
        {
            if ( isset($user[$property]))
            {
                $this->$property = $user[$property];
            }
        }

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
