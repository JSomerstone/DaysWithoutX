<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class UserModel
 * @package JSomerstone\DaysWithoutBundle\Model
 */
class UserModel implements ModelInterface
{
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
            : self::hashPassword($password, $nick);
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
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    private static function hashPassword($password, $nick)
    {
        return hash('sha256', "$nick-$password");
    }

}
