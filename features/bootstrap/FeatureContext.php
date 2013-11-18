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
    JSomerstone\DaysWithoutBundle\Storage\CounterStorage,
    JSomerstone\DaysWithoutBundle\Storage\UserStorage,
    JSomerstone\DaysWithoutBundle\Lib\StringFormatter;

use AssertContext as Assert;
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

    /**
     * @var JSomerstone\DaysWithoutBundle\Model\UserModel
     */
    protected $user;

    /**
     * @var JSomerstone\DaysWithoutBundle\Storage\CounterStorage
     */
    private $counterStorage;

    /**
     * @var JSomerstone\DaysWithoutBundle\Storage\UserStorage
     */
    private $userStorage;

    private static $counterStoragePath = '/tmp/dayswithout-test/counters';
    private static $userStoragePath = '/tmp/dayswithout-test/users';
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
        $this->userStorage = new UserStorage(self::$userStoragePath);

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
        //exec($command);
        echo "Done\n";
        exec("mkdir -p " . self::$counterStoragePath);
        exec("mkdir -p " . self::$userStoragePath);
    }

    /** @BeforeFeature */
    public static function prepareForTheFeature()
    {
        echo "Cleaning up temp-files ... ";
        exec("rm -rf " . self::$counterStoragePath . '/*');
        exec("rm -rf " . self::$userStoragePath . '/*');
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
            'counter' => array(
                'headline' => $counterHeadline,
                'public' => '',
                'owner' => array(
                    'nick' => '',
                    'password' => '',
                ),
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
            'counter' => array(
                'headline' => $headline,
                'private' => '',
                'owner' => array(
                    'nick' => $nick,
                    'password' => $password,
                ),
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
     * @Given /^"([^"]*)" has counter "([^"]*)" with "([^"]*)" days$/
     */
    public function hasCounterWithDays($nick, $headline, $days)
    {

    }


    /**
     * @Then /^page has "([^"]*)"$/
     */
    public function pageHas($expectedString)
    {
        Assert::false($this->response->isEmpty(), 'Unexpected empty page');
        Assert::contains(
            $expectedString,
            $this->response->getContent(),
            "Page did not have expected '$expectedString'"
        );
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
            sprintf('|<button.*>%s</button>|i', $textInButton),
            "Page did not have button '$textInButton'"
        );
    }

    /**
     * @Given /^user "([^"]*)" with password "([^"]*)"$/
     */
    public function userWithPassword($nick, $password)
    {
        $this->user = new UserModel($nick, $password);
        $this->userStorage->store($this->user);
    }

    /**
     * @Then /^the counter is "([^"]*)"$/
     */
    public function theCounterIs($counter)
    {
        $this->pageHas(
            "<div class=\"counterDays\">$counter</div>",
            "The page doesn't have counter at '$counter'"
        );
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
    public function userIsRedirectedTo($redirUrl)
    {
        Assert::true($this->response->isRedirection(), 'Not a redirection');
        Assert::true(
            $this->response->isRedirect($redirUrl),
            "Was not redirected to '$redirUrl'"
        );
    }

    private function pageMatchesRegexp($regexp, $messageIfNot = null)
    {
        $content = str_replace("\n", ' ', $this->response->getContent());
        Assert::regexp($regexp, $content, $messageIfNot);
    }

    private static function getCounterName($counterHeadline)
    {
        return StringFormatter::getUrlSafe($counterHeadline);
    }

    private function getRequestTokenFromResponse(Response $response)
    {
        $matches = array();
        preg_match(
            '/name="counter\[_token\]" value="(?P<token>[0-9a-z]+)"/',
            $response->getContent(),
            $matches
        );
        return isset($matches['token'])
            ? $matches['token']
            : null;
    }

}
