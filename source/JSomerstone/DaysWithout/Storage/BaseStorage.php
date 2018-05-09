<?php
/**
 * Created by PhpStorm.
 * User: joona
 * Date: 09/09/14
 * Time: 11:10
 */

namespace JSomerstone\DaysWithout\Storage;


abstract class BaseStorage
{
    const NOT_EQUALS = '$ne';
    const IN = '$in';

    /**
     * @var \MongoDb\Client
     */
    protected $mongoClient;

    /**
     * @var \MongoDatabase
     */
    protected $database;

    /**
     * @param \MongoDb\Client $mongoClient
     * @param $database
     * @throws StorageException
     */
    public function __construct(\MongoDb\Client $mongoClient, $database)
    {
        $this->mongoClient = $mongoClient;
        $this->database = $mongoClient->$database;
    }

    /**
     * @return \MongoCollection
     */
    abstract protected function getCollection();
}
