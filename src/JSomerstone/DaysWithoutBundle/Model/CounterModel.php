<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class CounterModel implements ModelInterface
{
    private $reseted;

    protected $headline;

    protected $name;

    /**
     * @var string
     */
    private $owner;

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
        $this->setOwner($owner);
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
            'owner' => is_object($this->owner)
                    ? $this->owner->getNick()
                    : $this->owner,
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

    public function setOwner(UserModel $user = null)
    {
        $this->owner = is_null($user) ? null : $user->getNick();
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function getDays()
    {
        $now = time();
        $reseted = strtotime($this->reseted);
        return (int)floor(($now - $reseted)/(60*60*24));
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
        $this->owner = isset($json->owner) ? $json->owner : null;
        $this->public = isset($json->public) ? $json->public : false;
        return $this;
    }

}
