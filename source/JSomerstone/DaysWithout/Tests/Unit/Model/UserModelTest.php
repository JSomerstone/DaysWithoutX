<?php

namespace JSomerstone\DaysWithout\Tests\Model;

use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Model\UserModel;

/**
 * @group model
 */
class UserModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function passwordIsHashed()
    {
        $nick = 'Dude';
        $password = 'S3cr37P4zzwörd!';

        $user = new UserModel($nick, $password);

        $this->assertEquals($nick, $user->getNick());
        $this->assertNotEquals($password, $user->getPassword());
    }

    /**
     * @test
     */
    public function nameSetterWorks()
    {
        $user = new UserModel(null);
        $user->setNick('testnick');

        $this->assertEquals('testnick', $user->getNick());
    }

    /**
     * @test
     */
    public function settingNameSetsId()
    {
        $user = new UserModel(null);
        $user->setNick('TestUser');
        $this->assertEquals('testuser', $user->getId());
    }

    /**
     * @test
     */
    public function setPasswordIsHashed()
    {
        $user = new UserModel('Relevant');
        $plainTextPassword = 'PlainTextPassword';
        $user->setPassword($plainTextPassword);

        $this->assertNotEquals($plainTextPassword, $user->getPassword());
    }

    /**
     * @test
     */
    public function passwordWithoutNickThrowsException()
    {
        $user = new UserModel(null);
        $this->setExpectedException('LogicException');
        $user->setPassword('any password without nick fails');
    }

    /**
     * @test
     */
    public function isSameAsDetectsIdenticalNicks()
    {
        $original = new UserModel('Original');
        $clone = new UserModel('Original');
        $someOneElse = new UserModel('NotOriginal');

        $this->assertTrue($original->isSameAs($clone));
        $this->assertFalse($original->isSameAs($someOneElse));
    }
}
