<?php

namespace JSomerstone\DaysWithoutBundle\Tests\Model;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group model
 */
class UserModelTest extends WebTestCase
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
        $this->assertEquals(
            hash('sha256', "$nick-$password"),
            $user->getPassword()
        );
    }

    /**
     * @test
     */
    public function nameSetterWorks()
    {
        $user = new UserModel();
        $user->setNick('testnick');

        $this->assertEquals('testnick', $user->getNick());
    }

    /**
     * @test
     */
    public function settingNameSetsId()
    {
        $user = new UserModel();
        $user->setNick('TestUser');
        $this->assertEquals('testuser', $user->getId());
    }

    /**
     * @test
     */
    public function setPasswordIsHashed()
    {
        $user = new UserModel('Relevant');
        $user->setPassword('PlainTextPassword');
        $expected = hash('sha256', "Relevant-PlainTextPassword");
        $this->assertEquals($expected, $user->getPassword());
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
}
