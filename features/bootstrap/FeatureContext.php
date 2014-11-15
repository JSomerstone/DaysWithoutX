<?php
include_once __DIR__ . '/../../app/AppKernel.php';
include_once __DIR__ . '/helper/FileHelper.php';
include_once __DIR__ . '/helper/debug.php';

use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\KernelInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

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
    static $DB_HOST = 'mongodb://localhost:27017';
    static $DB_NAME = 'dayswithout-test';
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

    /**
     * @var MongoClient
     */
    protected $mongoClient;

    protected $post = array();
    protected $get = array();
    protected $server = array();
    protected $requestToken;

    protected $systemUsers = array();

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

    private static $testUserPassword = 'testpassword';

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->mongoClient = new MongoClient(self::$DB_HOST);

        $this->counterStorage = new CounterStorage($this->mongoClient, self::$DB_NAME);
        $this->userStorage = new UserStorage($this->mongoClient, self::$DB_NAME);

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

    /** @BeforeScenario */
    public static function prepareForTheFeature()
    {
        $client = new MongoClient(self::$DB_HOST);
        $client->dropDB(self::$DB_NAME);
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns HttpKernel instance.
     *
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * @Given /^anonymeus user$/
     */
    public function anonymeusUser()
    {
        //throw new PendingException();
    }

    /**
     * @Given /^system has counters:$/
     */
    public function systemHasCounters(TableNode $table)
    {
        $hash = $table->getHash();
        foreach ( $hash as $row)
        {
            $owner = $row['Owner'] ? new UserModel($row['Owner']) : null;
            $resetDate = date('Y-m-d', time() - 60 * 60 * 24 * (int)$row['Days']);
            $createdDate = isset($row['Created']) ? new \DateTime($row['Created']) : null;
            $visibility = isset($row['Visibility']) ? $row['Visibility'] : CounterModel::VISIBILITY_PUBLIC;
            $counterModel = new CounterModel(
                $row['Counter'],
                $resetDate,
                $owner,
                $createdDate,
                $visibility
            );

            $this->counterStorage->store($counterModel);
        }
    }

    /**
     * @When /^"([^"]*)" page is loaded$/
     */
    public function pageIsLoaded($uri)
    {
        $this->request = Request::create($uri, 'GET', $parameters = array());
        $this->response = $this->getKernel()->handle($this->request);
        $token = $this->getRequestTokenFromResponse($this->response);
        if ($token)
        {
            $this->requestToken = $token;
        }
    }

    /**
     * @When /^user "([^"]*)" tries to log in with password "([^"]*)"$/
     */
    public function useTriesToLogInWithPassword($userName, $password)
    {
        $post = array(
            'form' => array(
                'nick' => $userName,
                'password' => $password,
                '_token' => $this->requestToken
            )
        );
        $this->response = $this->handlePostRequest('/login', $post);
    }

    /**
     * @param $url
     * @param array $post
     * @return Response
     */
    private function handlePostRequest($url, array $post)
    {
        $this->request = Request::create(
            $url,
            'POST',
            $post
        );
        return $this->getKernel()->handle($this->request);
    }

    /**
     * @When /^user posts new counter "([^"]*)"$/
     */
    public function userPostsNewCounter($counterHeadline)
    {
        $post = array(
            'headline' => $counterHeadline,
            'public' => '',
        );

        $this->response = $this->handlePostRequest('/create', $post);
    }

    /**
     * @When /^"([^"]*)" posts (public|protected|private) counter "([^"]*)" with password "([^"]*)"$/
     * @When /^"([^"]*)" posts (public|protected|private) counter "([^"]*)"$/
     */
    public function UserPostsPrivateCounter($nick, $visibility, $headline, $password = null)
    {
        $post = array(
            'headline' => $headline,
            $visibility => '',
        );
        $this->response = $this->handlePostRequest('/create', $post);
    }

    /**
     * @When /^user resets counter "([^"]*)"$/
     */
    public function userResetsCounter($counterHeadline)
    {
        return $this->resetsCounterWithPassword(null, $counterHeadline, null);

    }

    /**
     * @When /^"([^"]*)" resets counter "([^"]*)" with password "([^"]*)"$/
     * @When /^user "([^"]*)" resets the counter "([^"]*)" with password "([^"]*)"$/
     */
    public function resetsCounterWithPassword($userName, $counterHeadline, $password)
    {
        $url = sprintf(
            "/%s%s",
            self::getCounterName($counterHeadline),
            $userName ? "/$userName": ''
        );
        $post = array(
            'form' => array(
                'nick' => $userName,
                'password' => $password,
                'reset' => '',
                '_token' => $this->requestToken
            )
        );
        $this->response = $this->handlePostRequest($url, $post);
    }

    /**
     * @When /^user "([^"]*)" signs up with passwords "([^"]*)" and "([^"]*)"$/
     */
    public function userSignsUpWithPasswordsAnd($nick, $password1, $password2)
    {
        $post = array(
            'nick' => $nick,
            'password' => $password1,
            'password-confirm' => $password2,
            'send' => '',
            '_token' => $this->requestToken
        );
        $this->response = $this->handlePostRequest('/signup', $post);
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
            " - Did not"
        );
    }

    /**
     * @Given /^page doesn\'t have "([^"]*)"$/
     */
    public function pageDoesntHave($expectedString)
    {
        Assert::false($this->response->isEmpty(), 'Unexpected empty page');
        Assert::notContains(
            $expectedString,
            $this->response->getContent(),
            " - But did"
        );
    }

    /**
     * @Given /^public counter "([^"]*)" with "([^"]*)" days$/
     */
    public function publicCounterWithDays($headline, $days)
    {
        $this->storeCounter(
            $headline,
            time() - 60 * 60 * 24 * $days
        );
    }

    /**
     * @Given /^user "([^"]*)" has (public|protected|private) counter "([^"]*)" with "([^"]*)" days$/
     */
    public function userHasCounterWithDays($nick, $visibility, $headline, $days)
    {
        $this->storeCounter(
            $headline,
            time() - 60 * 60 * 24 * $days,
            new UserModel($nick, self::$testUserPassword),
            $visibility
        );
    }

    private function storeCounter($headline, $date, $user = null, $visibility = 'public')
    {
        $counterModel = new CounterModel(
            $headline,
            date('Y-m-d', $date),
            $user
        );
        $counterModel->setVisibility($visibility);
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
            sprintf('|%s</button>|i', $textInButton),
            "Page did not have button '$textInButton'"
        );
    }

    /**
     * @Given /^page does not have button "([^"]*)"$/
     */
    public function pageDoesNotHaveButtonPrivate($textInButton)
    {
        $this->pageNotMatchesRegexp(
            sprintf('|%s</button>|i', $textInButton),
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
        $this->systemUsers[$nick] = $password;
    }

    /**
     * @Given /^user "([^"]*)" is logged in$/
     */
    public function userIsLoggedIn($nick)
    {
        if ( ! isset($this->systemUsers[$nick]))
        {
            throw new \Exception("System doesn't have use '$nick'");
        }
        $this->pageIsLoaded('/login');
        $this->useTriesToLogInWithPassword($nick, $this->systemUsers[$nick]);
    }

    /**
     * @Then /^the counter is "([^"]*)"$/
     */
    public function theCounterIs($counter)
    {
        $this->pageHas(
            "<div class=\"counter-days\">$counter</div>",
            "The page doesn't have counter at '$counter'"
        );
    }

    /**
     * @Then /^page has link "([^"]*)" to "([^"]*)"$/
     */
    public function pageHasLink($linkText, $linkUrl)
    {
        $this->pageMatchesRegexp(
            sprintf('|<a href="http://localhost(:[0-9]+)?%s" .*>%s</a>|', $linkUrl, $linkText),
            " - Did not"
        );
    }

    /**
     * @Then /^user is redirected to "([^"]*)"$/
     */
    public function userIsRedirectedTo($redirUrl)
    {
        Assert::true($this->response->isRedirection(), 'Not a redirection' . $this->response->getContent());
        Assert::true(
            $this->response->isRedirect($redirUrl),
            " - Was not " . $this->response->getContent()
        );
        $this->pageIsLoaded($redirUrl);
    }

    /**
     * @When /^user deletes counter "([^"]*)" by "([^"]*)"$/
     */
    public function userDeletesCounter($counter, $owner)
    {
        $url = sprintf("/delete/%s/%s", $counter, $owner);
        $this->response = $this->handlePostRequest($url, array());
    }

    /**
     * @Then /^counter "([^"]*)" by "([^"]*)" doesn\'t exist$/
     * @Then /^counter "([^"]*)" doesn\'t exist$/
     */
    public function counterByDoesNotExist($counter, $owner = null)
    {
        Assert::false(
            $this->counterStorage->exists($counter, $owner),
            'But it did'
        );
    }

    /**
     * @Then /^json response has message "([^"]*)"$/
     */
    public function jsonResponseHasMessage($expectedMessage)
    {
        $response = json_decode($this->response->getContent(), true);
        if ( ! isset($response['message']))
        {
            throw new Exception('No message in response: ' . $this->response->getContent());
        }
        Assert::equals(
            $expectedMessage,
            $response['message']
        );
    }

    private function pageMatchesRegexp($regexp, $messageIfNot = null)
    {
        Assert::regexp($regexp, $this->response->getContent(), $this->response->getContent());
    }

    private function pageNotMatchesRegexp($regexp)
    {
        Assert::notRegexp($regexp, $this->response->getContent(), $this->response->getContent());
    }

    private static function getCounterName($counterHeadline)
    {
        return StringFormatter::getUrlSafe($counterHeadline);
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
