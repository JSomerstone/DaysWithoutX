<?php
namespace JSomerstone\DaysWithout\Storage;

use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Lib\StringFormatter;
use JSomerstone\DaysWithout\Model\UserModel;

class CounterStorage extends BaseStorage
{
    const COLLECTION = 'counter';
    const SANITY_LIMIT = 200;

    /**
     *
     * @param string $name
     * @param string $owner Optional, Nick of the user owning counter
     * @return \JSomerstone\DaysWithout\Model\CounterModel
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
     * @param \JSomerstone\DaysWithout\Model\CounterModel $counter
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
     * @param CounterModel $counter
     * @return $this
     * @throws StorageException
     */
    public function remove(CounterModel $counter)
    {
        $result = $this->getCollection()->remove(array(
            'name' => $counter->getName(),
            'owner' => $counter->getOwnerId()
        ));
        if ($result['err'])
        {
            throw new StorageException('Removing counter failed');
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
            ->find([
                'visibility' => [
                    self::NOT_EQUALS => CounterModel::VISIBILITY_PRIVATE
                ]
            ])
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
            ->find([
                'visibility' => [
                    self::NOT_EQUALS => CounterModel::VISIBILITY_PRIVATE
                ]
            ])
            ->sort(array('reseted' => -1))
            ->skip($skip)
            ->limit($limit);

        return $this->getResultsFromCursor($cursor);
    }

    /**
     * @param string $nick
     * @param bool $includePrivates true to include private counters, default false
     * @return array
     */
    public function getUsersCounters($nick, $includePrivates = false)
    {
        $acceptableVisibilities = [
            CounterModel::VISIBILITY_PUBLIC,
            CounterModel::VISIBILITY_PROTECTED
        ];
        if ( $includePrivates )
        {
            $acceptableVisibilities[] = CounterModel::VISIBILITY_PRIVATE;
        }

        $cursor = $this->getCollection()
            ->find([
                'owner' => $nick,
                'visibility' => [
                    self::IN => $acceptableVisibilities
                ]
            ])
            ->sort(array('reseted' => -1))
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
