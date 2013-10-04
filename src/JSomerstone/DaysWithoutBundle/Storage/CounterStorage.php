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
        return unserialize($data);
    }

    /**
     * Check if given counter exists or not
     * @param string $name
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
     */
    public function store(CounterModel $counter)
    {
        $owner = is_null($counter->getOwner())
                ? 'public'
                : $counter->getOwner()->getNick();

        $filePath = $this->getFilePath($owner);
        $filename = $this->getFileName(
            $counter->getName(),
            $owner
        );
        if ( ! file_exists($filePath))
        {
            mkdir($filePath);
        }
        if ( ! file_put_contents($filename, serialize($counter)))
        {
            throw new StorageException("Unable to perist counter to '$filename'");
        }
    }

    private function getFilePath($owner)
    {
        return "$this->basePath/$owner";
    }

    private function getFileName($name, $owner)
    {
        $counter = CounterModel::getUrlSafe($name);

        return "$this->basePath/$owner/$counter.txt";
    }
}