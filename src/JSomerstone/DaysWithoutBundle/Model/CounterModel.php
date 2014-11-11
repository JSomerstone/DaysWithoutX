<?php
namespace JSomerstone\DaysWithoutBundle\Model;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

class CounterModel implements ModelInterface
{
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PRIVATE = 'private';

    private $visiblity = self::VISIBILITY_PUBLIC;
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

    /**
     *
     * @param string $headline The headline of the counter
     * @param string $resetDate Optional, date in format YYYY-mm-dd
     * @param \JSomerstone\DaysWithoutBundle\Model\UserModel $owner, Optional
     * @param \DateTime|null $created optional
     * @param string $visibility, optional a self::VISIBILITY_ constant, default PUBLIC
     */
    public function __construct(
        $headline,
        $resetDate = null,
        UserModel $owner = null,
        \DateTime $created = null,
        $visibility = self::VISIBILITY_PUBLIC
    )
    {
        $this->headline = $headline;
        $this->reseted = is_null($resetDate) ? date('Y-m-d') : $resetDate;
        $this->name = StringFormatter::getUrlSafe($headline);
        $this->owner = $owner;
        $this->setVisibility($visibility);

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
            isset($properties['created']) ? new \DateTime($properties['created']) : null,
            isset($properties['visibility']) ? $properties['visibility'] : self::VISIBILITY_PUBLIC
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
            'visibility' => $this->visiblity,
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

    /**
     * @param string $visibility a self::VISIBILITY_* constant
     * @throws \InvalidArgumentException
     */
    public function setVisibility($visibility)
    {
        switch ($visibility)
        {
            case self::VISIBILITY_PUBLIC:
                $this->setPublic();
                break;
            case self::VISIBILITY_PROTECTED:
                $this->setProtected();
                break;
            case self::VISIBILITY_PRIVATE:
                $this->setPrivate();
                break;
            default:
                throw new \InvalidArgumentException("Unrecognized visibility '$visibility'");
        }
    }

    public function setPublic()
    {
        $this->visiblity = self::VISIBILITY_PUBLIC;
        return $this;
    }

    public function setProtected()
    {
        if ( ! isset($this->owner))
        {
            throw new \LogicException("Cannot set counter without owner as 'protected'");
        }
        $this->visiblity = self::VISIBILITY_PROTECTED;
        return $this;
    }

    public function setPrivate()
    {
        if ( ! isset($this->owner))
        {
            throw new \LogicException("Cannot set counter without owner as 'private'");
        }
        $this->visiblity = self::VISIBILITY_PRIVATE;
        return $this;
    }

    public function isPublic()
    {
        return (self::VISIBILITY_PUBLIC === $this->visiblity);
    }

    public function isProtected()
    {
        return (self::VISIBILITY_PROTECTED === $this->visiblity);
    }

    public function isPrivate()
    {
        return (self::VISIBILITY_PRIVATE === $this->visiblity);
    }

    public function isResettable()
    {
        return ($this->getDays() >= 1);
    }
}
