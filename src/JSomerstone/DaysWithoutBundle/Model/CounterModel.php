<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

class CounterModel implements ModelInterface
{
    private $reseted;

    /**
     * @var \DateTime
     */
    private $created;

    protected $headline;

    protected $name;

    /**
     * @var UserModel|null
     */
    private $owner;

    private $public;

    /**
     *
     * @param string $headline The headline of the counter
     * @param string $resetDate Optional, date in format YYYY-mm-dd
     * @param \JSomerstone\DaysWithoutBundle\Model\UserModel $owner, Optional
     * @param \DateTime|null $created optional
     */
    public function __construct($headline, $resetDate = null, UserModel $owner = null, \DateTime $created = null)
    {
        $this->headline = $headline;
        $this->reseted = is_null($resetDate) ? date('Y-m-d') : $resetDate;
        $this->name = StringFormatter::getUrlSafe($headline);
        $this->owner = $owner;
        $this->public = is_null($owner);

        $this->created = $created ?: new \DateTime();
    }

    /**
     * Instantiate new CounterModel from array
     * @param array $properties
     * @return CounterModel
     */
    public static function fromArray(array $properties)
    {
        return new CounterModel(
            isset($properties['headline']) ? $properties['headline'] : null,
            isset($properties['reseted']) ? $properties['reseted'] : null,
            isset($properties['owner']) ? new UserModel($properties['owner']) : null,
            isset($properties['created']) ? new \DateTime($properties['created']) : null
        );
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
            'owner' => $this->getOwnerId(),
            'public' => $this->public,
            'created' => $this->created->format('Y-m-d H:i:s')
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

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param UserModel|null $user
     * @return $this
     */
    public function setOwner(UserModel $user = null)
    {
        $this->owner = $user;
        return $this;
    }

    /**
     * @return null|UserModel
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function getOwnerId()
    {
        return is_object($this->owner)
            ? $this->owner->getNick()
            : null;
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
}
