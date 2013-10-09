<?php
include_once __DIR__ . '/../../app/AppKernel.php';

use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;
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
    protected $requestToken;

    protected $user;

    private $counterStorage;
    private static $counterStoragePath = '/tmp/dayswithout-test/counters';
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

    /**
     * @BeforeSuite
     */
    public static function prepareForSuite()
    {
        $command = __DIR__ . "/../../app/console cache:clear --env=test";
        echo "Cleaning up cache ... ";
        exec($command);
        echo "Done\n";
    }

    /** @BeforeFeature */
    public static function prepareForTheFeature()
    {
        echo "Cleaning up temp-files ... ";
        exec("rm -rf " . self::$counterStoragePath);
        mkdir(self::$counterStoragePath, 0770, true);
        echo "Done\n\n";
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
        $token = $this->getRequestTokenFromResponse($this->response);
        if ($token)
        {
            $this->requestToken = $token;
        }
    }

    /**
     * @When /^user posts new counter "([^"]*)"$/
     */
    public function userPostsNewCounter($counterHeadline)
    {
        $post = array(
            'form' => array(
                'thing' => $counterHeadline,
                'public' => '',
                '_token' => $this->requestToken
            )
        );
        $this->request = Request::create(
            '/create',
            'POST',
            $post
        );
        $this->response = $this->getKernel()->handle($this->request);
    }

    /**
     * @When /^"([^"]*)" posts private counter "([^"]*)" with password "([^"]*)"$/
     */
    public function UserPostsPrivateCounter($nick, $headline, $password)
    {
        $post = array(
            'form' => array(
                'thing' => $headline,
                'private' => '',
                'nick' => $nick,
                'password' => $password,
                '_token' => $this->requestToken
            )
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

    /**
     * @Then /^page has button "([^"]*)"$/
     */
    public function pageHasButton($textInButton)
    {
        $this->pageMatchesRegexp(
            sprintf('|<button(.[^<>])*>%s</button>|i', $textInButton),
            "Page did not have button '$textInButton'"
        );
    }

    /**
     * @Given /^user "([^"]*)" with password "([^"]*)"$/
     */
    public function userWithPassword($nick, $password)
    {
        $this->user = array(
            'nick' => $nick,
            'password' => $password
        );
    }

    /**
     * @Then /^the counter is "([^"]*)"$/
     */
    public function theCounterIs($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^page has link "([^"]*)"$/
     */
    public function pageHasLink($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^user is redirected to "([^"]*)"$/
     */
    public function userIsRedirectedTo($arg1)
    {
        var_dump(
            $this->response->isRedirection(),
            $this->response->isRedirect($arg1),
            $this->response->getContent()
        );
        throw new PendingException();
    }



    private function pageMatchesRegexp($regexp, $messageIfNot = null)
    {
        $content = str_replace("\n", ' ', $this->response->getContent());
        if ( preg_match($regexp, $content) !== 1)
        {
            throw new Exception(
                $messageIfNot ?: "Page did match regexp '$regexp'"
            );
        }
    }

    private static function getCounterName($counterHeadline)
    {
        return CounterModel::getUrlSafe($counterHeadline);
    }

    private function getRequestTokenFromResponse(Response $response)
    {
        $matches = array();
        preg_match(
            '/name="form\[_token\]" value="(?P<token>[0-9a-z]+)"/',
            $response->getContent(),
            $matches
        );
        return isset($matches['token'])
            ? $matches['token']
            : null;
    }
}
