<?php
namespace JSomerstone\DaysWithoutBundle\Model;

class UserModel
{
    private $nick;
    private $password;

    public function __construct($nick, $password)
    {
        $this->nick = $nick;
        $this->password = self::hashPassword($password, $nick);
    }

    public function getNick()
    {
        return $this->nick;
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