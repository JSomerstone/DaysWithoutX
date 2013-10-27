<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

class CounterStorage
{
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
     */
    public function store(CounterModel $counter)
    {
        $name = $counter->getName();
        $owner = is_null($counter->getOwner())
                ? 'public'
                : $counter->getOwner()->getNick();

        $filePath = $this->getFilePath($owner);
        $filename = $this->getFileName($name, $owner);
        if ( ! file_exists($filePath))
        {
            mkdir($filePath);
        }
        if ( ! file_put_contents($filename, serialize($counter)))
        {
            throw new StorageException("Unable to persist counter to '$filename'");
        }
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
}