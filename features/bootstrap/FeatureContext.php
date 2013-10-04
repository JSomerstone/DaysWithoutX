<?php
include_once __DIR__ . '/../../app/AppKernel.php';

use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Symfony\Component\HttpFoundation\Request;
use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Storage\CounterStorage;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    use Behat\Symfony2Extension\Context\KernelDictionary;

    /**
     *
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     *
     * @var Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    protected $post = array();
    protected $get = array();
    protected $server = array();

    private $counterStorage;
    private static $counterStoragePath = '/tmp/dayswithout-behat';
    private static $testUserPassword = 'testpassword';

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->counterStorage = new CounterStorage(self::$counterStoragePath);
        $this->setKernel(new AppKernel('test', false));
        // Initialize your context here
    }

    /** @BeforeFeature */
    public static function prepareForTheFeature()
    {
        $command = __DIR__ . "/../../app/console cache:clear --env=test";
        echo "Cleaning up cache ... ";
        exec($command);
        echo "Done\n\n";
        exec("rm -rf " . self::$counterStoragePath);
        mkdir(self::$counterStoragePath);
    }

    /**
     * @AfterFeature
     */
    public static function cleanupAfterFeature()
    {
        //exec("rm -rf " . self::$counterStoragePath);
    }


    /**
     * @Given /^anonymeus user$/
     */
    public function anonymeusUser()
    {
        //throw new PendingException();
    }

    /**
     * @When /^"([^"]*)" page is loaded$/
     */
    public function pageIsLoaded($uri)
    {
        $this->request = Request::create($uri);
        $this->response = $this->getKernel()->handle($this->request);
        //throw new PendingException();
    }

    /**
     * @When /^user posts new counter "([^"]*)"$/
     */
    public function userPostsNewCounter($counterHeadline)
    {
        $post = array(
            'thing' => $counterHeadline
        );
        $this->request = Request::create(
            '/create',
            'POST',
            $post
        );
        $this->response = $this->getKernel()->handle($this->request);
    }

    /**
     * @When /^user resets counter "([^"]*)"$/
     */
    public function userResetsCounter($counterHeadline)
    {
        $url = self::getCounterName($counterHeadline);
        $this->request = Request::create(
            "/$url",
            'POST',
            array('reset' => 1)
        );
        $this->response = $this->getKernel()->handle($this->request);

    }

    /**
     * @Then /^page has "([^"]*)"$/
     */
    public function pageHas($expectedString)
    {
        if ($this->response->isEmpty())
        {
            throw new Exception('Unexpected empty page');
        }
        if ( stripos($this->response->getContent(), $expectedString) === false)
        {
            echo $this->response->getContent();
            throw new Exception("Page did not have expected '$expectedString");
        }
    }

    /**
     * @Given /^"([^"]*)" counter "([^"]*)" with "([^"]*)" days exists$/
     */
    public function counterWithDaysExists($nick, $thing, $days)
    {
        $reseted = time() - 60 * 60 * 24 * $days;
        $user = $nick == 'public' ? null : new UserModel($nick, self::$testUserPassword);

        $counterModel = new CounterModel(
            $thing,
            date('Y-m-d', $reseted),
            $user
        );
        $this->counterStorage->store($counterModel);
    }

    /**
     * @Then /^the page exists$/
     */
    public function thePageExists()
    {
        if ($this->response->getStatusCode() === 404) {
            throw new \Exception('Response returned with status 404');
        }
    }

    private static function getCounterName($counterHeadline)
    {
        return CounterModel::getUrlSafe($counterHeadline);
    }
}