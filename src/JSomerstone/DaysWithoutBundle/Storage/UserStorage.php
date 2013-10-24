<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

class UserStorage
{
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }
    /**
     *
     * @param string $name
     * @return \JSomerstone\DaysWithoutBundle\Model\UserModel
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
     * @param UserModel $user
     * @throws StorageException
     */
    public function store(UserModel $user)
    {
        $filename = $this->getFileName($user->getNick());

        if ( ! file_put_contents($filename, serialize($user)))
        {
            throw new StorageException("Unable to persist user to '$filename'");
        }
    }

    private function getFileName($name)
    {
        $name = StringFormatter::getUrlSafe($name);

        return "$this->basePath/$name.txt";
    }
}