<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use JSomerstone\DaysWithoutBundle\Model\UserModel;

class CounterStorage extends BaseStorage
{
    const COLLECTION = 'counter';

    /**
     *
     * @param string $name
     * @param string $owner Nick of the user owning counter, default "public"
     * @return \JSomerstone\DaysWithoutBundle\Model\CounterModel
     * @throws StorageException
     */
    public function load($name, $owner = null)
    {
        $cursor = $this->getCollection()
            ->find($this->getCounterQuery($name, $owner));

        return $cursor->hasNext()
            ? $this->fromArray($cursor->getNext())
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
            'owner' => $owner
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
        $count = $this->getCollection()
            ->find($this->getCounterQuery($name, $owner))
            ->count();
        return $count === 1;
    }

    /**
     *
     * @param \JSomerstone\DaysWithoutBundle\Model\CounterModel $counter
     * @throws StorageException
     * @return CounterStorage
     */
    public function store(CounterModel $counter)
    {
        $result = $this->getCollection()->update(
            $this->getCounterQuery($counter->getName(), $counter->getOwner()),
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
     * @param $counterArray
     * @return CounterModel
     */
    private function fromArray($counterArray)
    {
        $model =  new CounterModel(
           isset($counterArray['headline']) ? $counterArray['headline'] : null,
           isset($counterArray['reseted']) ? $counterArray['reseted'] : null,
           isset($counterArray['owner']) ? new UserModel($counterArray['owner']) : null
        );
        return $model;
    }
}
