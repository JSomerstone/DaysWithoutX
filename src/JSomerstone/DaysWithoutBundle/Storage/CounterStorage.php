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

    const FORMAT_JSON = 'json';
    const FORMAT_SERIALIZED = 'serialized';

    private $format;

    /**
     * @param \MongoClient $mongoClient
     * @param $database
     * @param string $format Optional, 'json' or 'serialized'
     * @throws StorageException
     */
    public function __construct(\MongoClient $mongoClient, $database, $format = self::FORMAT_JSON)
    {
        $this->mongoClient = $mongoClient;
        $this->database = $mongoClient->$database;

        if ( ! in_array($format, array(self::FORMAT_JSON, self::FORMAT_SERIALIZED)))
        {
            throw new StorageException('Unsupported storing format: ' . $format);
        }
        $this->format = $format;
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
        $query = array(
            'name' => StringFormatter::getUrlSafe($name),
            'owner' => is_null($owner)
                    ? null
                    : $owner
        );
        $cursor = $this->getCollection()->find( $query );

        return $cursor->hasNext()
            ? $this->fromArray($cursor->getNext())
            : null;
    }

    /**
     * Check if given counter exists or not
     * @param string $name
     * @param string $owner
     * @return bool
     */
    public function exists($name, $owner = 'public')
    {
        return file_exists($this->getFileName($name, $owner));
    }

    /**
     *
     * @param \JSomerstone\DaysWithoutBundle\Model\CounterModel $counter
     * @throws StorageException
     * @return CounterStorage
     */
    public function store(CounterModel $counter)
    {
        $result = $this->getCollection()->insert($counter->toArray());
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

    private function getFilePath($owner)
    {
        return sprintf(
            "%s/%s",
            $this->basePath,
            StringFormatter::getUrlSafe($owner)
        );
    }

    private function getFileName($name, $owner)
    {
        return sprintf(
            "%s/%s/%s.txt",
            $this->basePath,
            StringFormatter::getUrlSafe($owner),
            StringFormatter::getUrlSafe($name)
        );
    }

    /**
     * @param string $counter Serialized or JSON-object according to $this->format
     * @return CounterModel
     * @throws StorageException if provided
     */
    private function unserialize($counter)
    {
        if ($this->format === self::FORMAT_JSON)
        {
            $counterModel = new CounterModel(null);
            return $counterModel->fromJsonObject(json_decode($counter));
        }
        else
        {
            return unserialize($counter);
        }
    }

    /**
     * @param CounterModel $counter
     * @return string
     */
    private function serialize(CounterModel $counter)
    {
        if ($this->format === self::FORMAT_JSON)
        {
            return $counter->toJson();
        }
        else
        {
            return serialize($counter);
        }
    }

    /**
     * @param $counterArray
     * @return CounterModel
     */
    private function fromArray($counterArray)
    {
        return new CounterModel(
           isset($counterArray['headline']) ? $counterArray['headline'] : null,
           isset($counterArray['reseted']) ? $counterArray['reseted'] : null,
           isset($counterArray['owner']) ? new UserModel($counterArray['owner']) : null
        );
    }
}
