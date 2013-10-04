<?php
namespace JSomerstone\DaysWithoutBundle\Model;

class CounterModel
{
    private $days;
    private $reseted;
    private $name;
    private $thing;

    /**
     *
     * @var UserModel
     */
    private $owner;
    private $public;

    /**
     *
     * @param string $thing The headline of the counter
     * @param string $reseted Optional, date in format YYYY-mm-dd
     * @param \JSomerstone\DaysWithoutBundle\Model\UserModel $owner, Optional
     */
    public function __construct($thing, $reseted = null, UserModel $owner = null)
    {
        $this->thing = $thing;
        $this->reseted = is_null($reseted) ? date('Y-m-d') : $reseted;
        $this->name = self::getUrlSafe($thing);
        $this->owner = $owner;
        $this->public = is_null($owner);
    }

    /**
     *
     * @return \JSomerstone\DaysWithoutBundle\Model\CounterModel
     */
    public function reset()
    {
        $this->reseted = date('Y-m-d');
        return $this;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'name' => $this->name,
            'thing' => $this->thing,
            'reseted' => $this->reseted,
            'days' => $this->getDays()
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getThing()
    {
        return $this->thing;
    }

    public function getReseted()
    {
        return $this->reseted;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getPublic()
    {
        return $this->public;
    }

    public function getDays()
    {
        $now = time();
        $reseted = strtotime($this->reseted);
        return floor(($now - $reseted)/(60*60*24));
    }

    /**
     *
     * @return string JSON-notation
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public static function getUrlSafe($unsafe)
    {
        $lower = strtolower($unsafe);
        $clean = preg_replace('/[^a-z0-9_\ \-]/', '', $lower);
        return preg_replace('/[\ ]/', '-', $clean);
    }
}