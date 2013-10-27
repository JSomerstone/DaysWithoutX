<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

class UserModel
{
    private $nick;
    private $password;

    public function __construct($nick = null, $password = null)
    {
        $this->nick = $nick;
        $this->id = StringFormatter::getUrlSafe($nick);
        $this->password = is_null($password)
            ? null
            : self::hashPassword($password, $nick);
    }

    public function setNick($nick)
    {
        $this->id = StringFormatter::getUrlSafe($nick);
        $this->nick = $nick;
    }

    public function getNick()
    {
        return $this->nick;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPassword($password)
    {
        if (!isset($this->nick)) {
            throw new \LogicException('Users nick must be set before password');
        }
        $this->password = self::hashPassword($password, $this->getNick());
    }

    public function getPassword()
    {
        return $this->password;
    }
    
    private static function hashPassword($password, $nick)
    {
        return hash('sha256', "$nick-$password");
    }
}