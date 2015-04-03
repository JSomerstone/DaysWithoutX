<?php

namespace Model;

use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Model\UserModel;

/**
 * @group model
 */
class CounterModelTest extends \PHPUnit_Framework_TestCase
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
        $pool = array(0, 1, 2, 3, 5, 8, 13, 21, 34, 55);
        foreach ($pool as $daysSince)
        {
            $then = time() - $daysSince * (60*60*24);
            $reset = date('Y-m-d', $then);
            $counter = new CounterModel(null, $reset);
            $this->assertEquals(
                $daysSince,
                $counter->getDays(),
                "Counter did not return expected day-count when given date '$reset'"
            );
        }
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
    public function settersAndGettersWorks()
    {
        $counter = new CounterModel('irrelevant');

        $name = uniqid('name');
        $counter->setName($name);
        $this->assertEquals($name, $counter->getName(), 'Name unexpected');

        $headline = uniqid('headline');
        $counter->setHeadline($headline);
        $this->assertEquals($headline, $counter->getHeadline(), 'Headline unexpected');

        $reseted = '2014-01-01';
        $counter->setReseted($reseted);
        $this->assertEquals($reseted, $counter->getReseted(), 'Reseted unexpected');
    }

    /**
     * @test
     */
    public function settingVisibilityWorks()
    {
        $owner = new UserModel('Dude');
        $counter = new CounterModel('irrelevant', null, $owner);

        $this->assertTrue($counter->isPublic());
        $this->assertFalse($counter->isProtected());
        $this->assertFalse($counter->isPrivate());
        $counter->setProtected();
        $this->assertFalse($counter->isPublic());
        $this->assertTrue($counter->isProtected());
        $this->assertFalse($counter->isPrivate());
        $counter->setPrivate();
        $this->assertFalse($counter->isPublic());
        $this->assertFalse($counter->isProtected());
        $this->assertTrue($counter->isPrivate());
    }

    public function provideValidProperties()
    {
        return array(
            'name' => array('name', 'meaning-of-life'),
            'headline' => array('headline', 'Meaning of Life'),
            'reset date' => array('reseted', new \DateTime()),
        );
    }

    public function provideHeadlineNamePairs()
    {
        return array(
            array('Abba', 'abba'),
            array('Abba Ac/Dc', 'abba-ac-dc'),
            array('abba-acdc', 'abba-acdc'),
            array('Q!"#¤%&(=rty', 'q-rty'),
            array('Fuu-bar 123', 'fuu-bar-123'),
        );
    }

    /**
     * @test
     */
    public function createdSettingAndGetting()
    {
        $fakeCreationDate = new \DateTime('now');

        $counter = new CounterModel('irrelevant', null, null, $fakeCreationDate);

        $this->assertSame(
            $fakeCreationDate,
            $counter->getCreated()
        );
    }

    /**
     * @test
     */
    public function isOwnedByDetectsCorrectOwner()
    {
        $alfred = new UserModel('Alfred');

        $counterUnderTest = new CounterModel('Alfred´s public counter');
        $counterUnderTest->setOwner($alfred);

        $this->assertTrue($counterUnderTest->isOwnedBy($alfred));
    }

    /**
     * @test
     */
    public function isOwnedByDetectsNonOwner()
    {
        $alfred = new UserModel('Alfred');
        $bertha = new UserModel('Bertha');

        $counterUnderTest = new CounterModel('Alfred´s public counter');
        $counterUnderTest->setOwner($alfred);

        $this->assertFalse($counterUnderTest->isOwnedBy($bertha));
    }

    /**
     * @test
     */
    public function newCounterDoesntHaveHistory()
    {
        $counter = new CounterModel('Without history', $this->yesterday);

        $this->assertEmpty($counter->getHistory());
    }

    /**
     * @test
     */
    public function resetedCounterHasHistory()
    {
        $counter = new CounterModel('Without history', $this->yesterday);
        $counter->reset();

        $expectedHistory = array(
            [
                'timestamp' => date('Y-m-d H:i:s'),
                'days' => 1,
                'comment' => null
            ]
        );

        $actualHistory = $counter->getHistory();
        $this->assertEquals($expectedHistory, $actualHistory);
    }

    /**
     * @test
     */
    public function resetedCommentIsStored()
    {
        $counter = new CounterModel('Without history', $this->yesterday);
        $counter->reset('Flesh is weak');

        $expectedHistory = array(
            [
                'timestamp' => date('Y-m-d H:i:s'),
                'days' => 1,
                'comment' => 'Flesh is weak'
            ]
        );

        $actualHistory = $counter->getHistory();
        $this->assertEquals($expectedHistory, $actualHistory);
    }

    /**
     * @test
     */
    public function toArrayHasHistory()
    {
        $counter = new CounterModel('Without history', $this->yesterday);
        $counter->reset('TEst');
        $asArray = $counter->toArray();

        $this->assertTrue(isset($asArray['history']), 'No history from CounterModel::toArray()');
    }

    /**
     * @test
     */
    public function fromArrayAcceptsHistory()
    {
        $counter = new CounterModel('Without history', $this->yesterday);
        $counter->reset('First and last reset of this counter');

        $clone = CounterModel::fromArray($counter->toArray());
        $this->assertEquals(
            $counter->toArray(),
            $clone->toArray()
        );
    }
}
