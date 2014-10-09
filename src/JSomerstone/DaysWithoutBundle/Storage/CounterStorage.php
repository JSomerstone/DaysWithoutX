<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use JSomerstone\DaysWithoutBundle\Model\UserModel;

class CounterStorage extends BaseStorage
{
    const COLLECTION = 'counter';
    const SANITY_LIMIT = 200;

    /**
     *
     * @param string $name
     * @param string $owner Optional, Nick of the user owning counter
     * @return \JSomerstone\DaysWithoutBundle\Model\CounterModel
     * @throws StorageException
     */
    public function load($name, $owner = null)
    {
        $result = $this->getCollection()
            ->findOne($this->getCounterQuery($name, $owner));

        return is_array($result)
            ? CounterModel::fromArray($result)
            : null;
    }

    /**
     * @param string $name
     * @param string $owner optional
     * @return array
     */
    private function getCounterQuery($name, $owner = null)
    {
        $query = array(
            'name' => StringFormatter::getUrlSafe($name),
            'owner' => is_null($owner) ? null : $owner
        );
        return $query;
    }

    /**
     * Check if given counter exists or not
     * @param string $name
     * @param string $owner
     * @return bool
     */
    public function exists($name, $owner = null)
    {
        $counter = $this->getCollection()
            ->findOne($this->getCounterQuery($name, $owner));
        return is_array($counter);
    }

    /**
     * Persist given CounterModel, update existing
     * @param \JSomerstone\DaysWithoutBundle\Model\CounterModel $counter
     * @throws StorageException
     * @return CounterStorage
     */
    public function store(CounterModel $counter)
    {
        $result = $this->getCollection()->update(
            $this->getCounterQuery($counter->getName(), $counter->getOwnerId()),
            $counter->toArray(),
            array('upsert' => true)
        );
        if ($result['err'])
        {
            throw new StorageException('Storing counter failed');
        }
        return $this;
    }

    /**
     * @return \MongoCollection
     */
    protected function getCollection()
    {
        return $this->database->{self::COLLECTION};
    }

    /**
     * @param int $limit optional, default 10
     * @param int $skip optional, default 0
     * @return array
     */
    public function getLatestCounters($limit = 10, $skip = 0)
    {
        $cursor = $this->getCollection()
            ->find()
            ->sort(array('created' => -1))
            ->skip($skip)
            ->limit($limit);

        return $this->getResultsFromCursor($cursor);
    }

    /**
     * @param int $limit optional, default 10
     * @param int $skip optional, default 0
     * @return array
     */
    public function getResentResetsCounters($limit = 10, $skip = 0)
    {
        $cursor = $this->getCollection()
            ->find()
            ->sort(array('reseted' => -1))
            ->skip($skip)
            ->limit($limit);

        return $this->getResultsFromCursor($cursor);
    }

    /**
     * @param string $nick
     * @param string $sortBy optional default 'reseted'
     * @param int $direction 1 | -1
     * @return array
     */
    public function getUsersCounters($nick, $sortBy = 'reseted', $direction = -1)
    {
        $cursor = $this->getCollection()
            ->find(array('owner' => $nick))
            ->sort(array($sortBy => $direction))
            ->limit(self::SANITY_LIMIT);

        return $this->getResultsFromCursor($cursor);
    }

    private function getResultsFromCursor(\MongoCursor $cursor)
    {
        $result = array();
        while ($cursor->hasNext())
        {
            $result[] = CounterModel::fromArray($cursor->getNext());
        }
        return $result;
    }
}
