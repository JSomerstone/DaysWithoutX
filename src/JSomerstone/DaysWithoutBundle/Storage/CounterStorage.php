<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

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
    public function load($name, $owner = 'public')
    {
        $filename = $this->getFileName($name, $owner);
        if ( ! file_exists($filename) || ! is_readable($filename))
        {
            throw new StorageException("Unable to read file from '$filename'");
        }

        $data = file_get_contents($filename);
        return $this->unserialize($data);
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
        $name = $counter->getName();
        $owner = is_null($counter->getOwner())
                ? 'public'
                : $counter->getOwner()->getNick();

        $collection = $this->database->{self::COLLECTION};
        $collection->insert($counter->toArray());
        return $this;
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
}
