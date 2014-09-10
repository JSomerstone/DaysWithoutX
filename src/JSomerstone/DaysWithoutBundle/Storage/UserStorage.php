<?php
namespace JSomerstone\DaysWithoutBundle\Storage;

use JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

class UserStorage extends BaseStorage
{
    const COLLECTION = 'users';

    /**
     * @return \MongoCollection
     */
    protected function getCollection()
    {
        return $this->database->{self::COLLECTION};
    }

    /**
     *
     * @param string $nick
     * @return \JSomerstone\DaysWithoutBundle\Model\UserModel
     * @throws StorageException
     */
    public function load($nick)
    {
        $result = $this->getCollection()
            ->findOne(array('nick' => $nick));

        return is_array($result)
            ? $this->fromArray($result)
            : null;
    }

    /**
     * @param array $user
     * @return UserModel
     */
    private function fromArray(array $user)
    {
        $userModel = new UserModel();
        return $userModel->fromArray($user);
    }

    /**
     * Check if given counter exists or not
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return $this->getCollection()->count(array('nick' => $name)) === 1;
    }

    /**
     * @param UserModel $user
     * @return UserStorage
     * @throws StorageException
     */
    public function store(UserModel $user)
    {
        $result = $this->getCollection()->update(
            array(
                'nick' => $user->getNick()
            ),
            $user->toArray(),
            array('upsert' => true)
        );
        if ($result['err'])
        {
            throw new StorageException('Storing user failed');
        }
        return $this;
    }

    /**
     * Authenticates the user
     * @param UserModel $user
     * @return bool
     */
    public function authenticate(userModel $user)
    {
        $persisted = $this->load($user->getNick());
        return ($persisted->getPassword() === $user->getPassword());
    }

}
