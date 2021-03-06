<?php
namespace JSomerstone\DaysWithout\Storage;

use JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Lib\StringFormatter;

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
     * @return \JSomerstone\DaysWithout\Model\UserModel
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
        $userModel = new UserModel(null);
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
}
