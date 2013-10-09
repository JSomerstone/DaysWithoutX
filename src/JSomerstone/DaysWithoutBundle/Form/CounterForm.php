<?php
namespace JSomerstone\DaysWithoutBundle\Form;
class CounterForm
{
    private $thing;
    private $nick;
    private $password;
    private $public;
    private $private;

    public function setThing($t)
    {
        $this->thing = $t;
    }

    public function setNick($n)
    {
        $this->nick = $n;
    }

    public function setPassword($p)
    {
        $this->password = $p;
    }

    public function setPublic($btn)
    {
        $this->public = $btn;
    }

    public function setPrivate($btn)
    {
        $this->private = $btn;
    }

    public function getThing()
    {
        return $this->thing;
    }

    public function getNick()
    {
        return $this->nick;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPublic()
    {
        return $this->public;
    }

    public function getPrivate()
    {
        return $this->private;
    }
}
