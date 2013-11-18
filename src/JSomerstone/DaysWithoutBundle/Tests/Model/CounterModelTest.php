<?php

namespace JSomerstone\DaysWithoutBundle\Tests\Model;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group model
 */
class CounterModelTest extends WebTestCase
{
    protected $today;
    protected $yesterday;

    public function setUp()
    {
        $this->today = date('Y-m-d');
        $this->yesterday = date('Y-m-d', strtotime('-1 days'));
    }

    /**
     * @test
     */
    public function counterCanBeReset()
    {
        $counter = new CounterModel(null, $this->yesterday);

        $this->assertEquals(1, $counter->getDays());
        $counter->reset();
        $this->assertEquals(0, $counter->getDays());
        $this->assertEquals($this->today, $counter->getReseted());
    }

    /**
     * @test
     */
    public function counterCountsDaysCorrectly()
    {
        $pool = [0, 1, 2, 3, 5, 8, 13, 21, 34, 55];
        foreach ($pool as $daysSince)
        {
            $then = time() - $daysSince * (60*60*24);
            $reseted = date('Y-m-d', $then);
            $counter = new CounterModel(null, $reseted);
            $this->assertEquals(
                $daysSince,
                $counter->getDays(),
                "Counter did not return expected day-count when given date '$reseted'"
            );
        }
    }

    /**
     * @test
     */
    public function modelConvertionToJson()
    {
        $headline = 'Headline of the counter';
        $owner = new UserModel('testuser', null);

        $counter = new CounterModel($headline, $this->yesterday, $owner);

        $expected = json_encode([
            'name' => 'headline-of-the-counter',
            'headline' => $headline,
            'reseted' => $this->yesterday,
            'days' => 1,
            'owner' => array(
                'nick' => 'testuser',
                'id' => 'testuser',
                'password' => null
            ),
            'public' => false
        ]);
        $actual = $counter->toJson();
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @test
     */
    public function counterWithoutOwnerIsPublic()
    {
        $counter = new CounterModel(null);
        $this->assertTrue($counter->isPublic());
    }

    /**
     * @test
     * @dataProvider provideHeadlineNamePairs
     * @param type $headline
     * @param type $expectedName
     */
    public function counterConvertsNameFromHeadlineCorrectly($headline, $expectedName)
    {
        $counter = new CounterModel($headline);

        $this->assertEquals(
            $expectedName,
            $counter->getName()
        );

        $this->assertEquals(
            $headline,
            $counter->getHeadline()
        );
    }

    /**
     * @test
     */
    public function ownerIsSameAsProvided()
    {
        $owner = new UserModel('tst', null);
        $counter = new CounterModel(null, null, $owner);

        $this->assertSame(
            $owner,
            $counter->getOwner()
        );
    }

    /**
     * @test
     */
    public function fromJsonWithAllProperties()
    {
        $owner = new UserModel('irrelevant', 'irrelevant');
        $original = new CounterModel('Headline', date('Y-m-d'), $owner);
        $json = $original->toJson();
        $clone = (new CounterModel(null))->fromJsonObject(json_decode($json));

        $this->assertSame($original->toArray(), $clone->toArray());
    }

    /**
     * @test
     * @dataProvider provideValidProperties
     */
    public function settersAndGettersWorks($property, $value)
    {
        $counter = new CounterModel('irrelevant');
        $setter = 'set' . ucfirst($property);
        $getter = 'get' . ucfirst($property);

        $counter->$setter($value);
        $this->assertEquals(
            $value,
            $counter->$getter()
        );
    }

    /**
     * @test
     */
    public function settingPrivateOrPublicWorks()
    {
        $counter = new CounterModel('irrelevant');

        $counter->setPrivate();
        $this->assertFalse($counter->isPublic());
        $counter->setPublic();
        $this->assertTrue($counter->isPublic());
    }

    public function provideValidProperties()
    {
        return [
            'name' => ['name', 'meaning-of-life'],
            'headline' => ['headline', 'Meaning of Life'],
            'reset date' => ['reseted', date('Y-m-d')],
            'owner' => ['owner', new UserModel('irrelevant', 'irrelevant')],
        ];
    }

    public function provideHeadlineNamePairs()
    {
        return [
            ['Abba', 'abba'],
            ['Abba Ac/Dc', 'abba-acdc'],
            ['abba-acdc', 'abba-acdc'],
            ['Q!"#¤%&(=rty', 'qrty'],
            ['Fuu-bar 123', 'fuu-bar-123'],
        ];
    }
}