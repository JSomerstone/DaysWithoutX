<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use JSomerstone\DaysWithoutBundle\Model\UserModel;

class CounterStorage
{
    const COLLECTION = 'counter';

    /**
     * @var \MongoClient
     */
    private $mongoClient;

    /**
     * @var MongoDatabase
     */
    private $database;

    /**
     * @param \MongoClient $mongoClient
     * @param $database
     * @throws StorageException
     */
    public function __construct(\MongoClient $mongoClient, $database)
    {
        $this->mongoClient = $mongoClient;
        $this->database = $mongoClient->$database;
    }

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
     * @param $name
     * @param null $owner
     * @return array
     */
    private function getCounterQuery($name, $owner = null)
    {
        return array(
            'name' => StringFormatter::getUrlSafe($name),
            'owner' => $owner
        );
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
    private function getCollection()
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
