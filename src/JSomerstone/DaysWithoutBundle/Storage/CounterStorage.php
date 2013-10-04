<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel;

class CounterStorage
{
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     *
     * @param string $name
     * @return \JSomerstone\DaysWithoutBundle\Model\CounterModel
     * @throws StorageException
     */
    public function load($name)
    {
        $filename = $this->getFileName($name);
        if ( ! file_exists($filename) || ! is_readable($filename))
        {
            throw new StorageException("Unable to read file from '$filename'");
        }

        $data = file_get_contents($filename);
        return unserialize($data);
    }

    /**
     * Check if given counter exists or not
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return file_exists($this->getFileName($name));
    }

    /**
     *
     * @param \JSomerstone\DaysWithoutBundle\Model\CounterModel $counter
     * @throws StorageException
     */
    public function store(CounterModel $counter)
    {
        $filename = $this->getFileName($counter->getName());

        if ( ! file_put_contents($filename, serialize($counter)))
        {
            throw new StorageException("Unable to perist counter to '$filename'");
        }
    }

    private function getFileName($name)
    {
        $safe = CounterModel::getUrlSafe($name);
        return "$this->basePath/$safe.txt";
    }
}