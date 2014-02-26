<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class CounterModel
{
    /** @ODM\Date */
    private $reseted;

    /** @ODM\String */
    protected $headline;

    /** @ODM\String */
    protected $name;

    /**
     * @var UserModel
     * @ODM\ReferenceOne(targetDocument="UserModel", cascade="all")
     */
    private $owner;

    /** @ODM\Boolean */
    private $public;

    /**
     *
     * @param string $headline The headline of the counter
     * @param string $resetDate Optional, date in format YYYY-mm-dd
     * @param \JSomerstone\DaysWithoutBundle\Model\UserModel $owner, Optional
     */
    public function __construct($headline, $resetDate = null, UserModel $owner = null)
    {
        $this->headline = $headline;
        $this->reseted = is_null($resetDate) ? date('Y-m-d') : $resetDate;
        $this->name = StringFormatter::getUrlSafe($headline);
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
            'headline' => $this->headline,
            'reseted' => $this->reseted,
            'days' => $this->getDays(),
            'owner' => ($this->owner) ? $this->owner->getNick() : null,
            'public' => $this->public
        );
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    public function getName()
    {
        return $this->name;
    }

    public function setHeadline($headline)
    {
        $this->headline = $headline;
        $this->setName(StringFormatter::getUrlSafe($headline));
        return $this;
    }

    public function getHeadline()
    {
        return $this->headline;
    }

    public function setReseted($date)
    {
        $this->reseted = $date;
        return $this;
    }
    public function getReseted()
    {
        return $this->reseted;
    }

    public function setOwner(UserModel $user)
    {
        $this->owner = $user;
        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getDays()
    {
        $now = time();
        $reseted = strtotime($this->reseted);
        return floor(($now - $reseted)/(60*60*24));
    }

    public function setPublic()
    {
        $this->public = true;
        return $this;
    }

    public function setPrivate()
    {
        $this->public = false;
        return $this;
    }

    public function isPublic()
    {
        return $this->public;
    }

    /**
     *
     * @return string JSON-notation
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param object $json
     * @return $this
     */
    public function fromJsonObject($json)
    {
        $this->name =  isset($json->name) ? $json->name : null;
        $this->headline = isset($json->headline) ? $json->headline : null;
        $this->reseted = isset($json->reseted) ? $json->reseted : date('Y-m-d');
        if (isset($json->owner))
        {
            $this->owner = new UserModel($json->owner);
        }
        $this->public = isset($json->public) ? $json->public : false;
        return $this;
    }

}
