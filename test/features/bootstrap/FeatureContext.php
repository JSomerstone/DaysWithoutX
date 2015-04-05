<?php
include_once '/vagrant/test/bootstrap.behat.php';
include_once __DIR__ . '/helper/FileHelper.php';
include_once __DIR__ . '/helper/Curlifier.php';

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Storage\UserStorage,
    JSomerstone\DaysWithout\Lib\StringFormatter;

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
    const BASE_URL = 'http://localhost';

    static $DB_HOST = 'mongodb://localhost:27017';
    static $DB_NAME = 'dayswithout-test';

    /**
     *
     * @var string
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
     * @var JSomerstone\DaysWithout\Model\UserModel
     */
    protected $user;

    /**
     * @var JSomerstone\DaysWithout\Storage\CounterStorage
     */
    private $counterStorage;

    /**
     * @var JSomerstone\DaysWithout\Storage\UserStorage
     */
    private $userStorage;

    /**
     * @var Curlifier
     */
    private $curl;

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
        $this->curl = new Curlifier();
    }

    /**
     * @BeforeSuite
     */
    public static function prepareForSuite()
    {
        rename(
            '/vagrant/config/config.yml',
            '/vagrant/config/config.yml.backup'
        );
        copy(
            '/vagrant/config/config.behat.yml',
            '/vagrant/config/config.yml'
        );
    }

    /**
     * @AfterSuite
     */
    public static function cleanupAfterTests()
    {
        rename(
            '/vagrant/config/config.yml.backup',
            '/vagrant/config/config.yml'
        );
    }

    /** @BeforeScenario */
    public static function prepareForTheFeature()
    {
        $client = new MongoClient(self::$DB_HOST);
        $client->dropDB(self::$DB_NAME);
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
     * @When /^page "([^"]*)" is loaded$/
     */
    public function pageIsLoaded($uri)
    {
        $this->curl->setUrl(self::BASE_URL . $uri)
            ->setGet()
            ->setPost()
            ->request();

        $this->response = $this->curl->getBody();
    }

    /**
     * @When /^user "([^"]*)" tries to log in with password "([^"]*)"$/
     */
    public function useTriesToLogInWithPassword($userName, $password)
    {
        $post = array(
            'nick' => $userName,
            'password' => $password,
        );
        $this->response = $this->handlePostRequest('/api/login', $post);
    }

    /**
     * @param $url
     * @param array $post
     * @return string
     */
    private function handlePostRequest($url, array $post)
    {
        $this->curl->setUrl(self::BASE_URL . $url)
            ->setPost($post)
            ->setGet();

        return $this->curl->request()->getBody();
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
     * @When /^user resets counter "([^"]*)" by "([^"]*)"$/
     * @When /^user resets counter "([^"]*)" by "([^"]*)" with comment "([^"]*)"$/
     */
    public function userResetsCounter($counterHeadline, $owner = null, $comment = null, $userName = null, $password = null)
    {
        $url = sprintf(
            "/api/counter/reset/%s%s",
            self::getCounterName($counterHeadline),
            $owner ? "/$owner": ''
        );
        $post = array(
            'comment' => $comment,
            'username' => $userName,
            'password' => $password
        );
        $this->response = $this->handlePostRequest($url, $post);
    }

    /**
     * @When /^"([^"]*)" resets counter "([^"]*)" with password "([^"]*)"$/
     */
    public function resetsCounterWithPassword($username, $counterName, $password)
    {
        $this->userResetsCounter(
            $counterName,
            $username,
            null,
            $username,
            $password
        );
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
        );
        $this->response = $this->handlePostRequest('/api/signup', $post);
    }

    /**
     * @Then /^page has "([^"]*)"$/
     */
    public function pageHas($expectedString, $messageIfNot = null)
    {
        Assert::false(empty($this->response), 'Unexpected empty page');
        Assert::contains(
            $expectedString,
            $this->response,
            $messageIfNot
        );
    }

    /**
     * @Given /^page doesn\'t have "([^"]*)"$/
     */
    public function pageDoesntHave($expectedString)
    {
        Assert::false(empty($this->response), 'Unexpected empty page');
        Assert::notContains(
            $expectedString,
            $this->response,
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
        if ($this->curl->getHttpCode() === 404) {
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
     * @Then /^counter is "([^"]*)"$/
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
        #throw new Exception('Unimplemented');
        D($this->curl->getHttpCode(), $this->curl->getBody());

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
            "Counter '$counter' does exist"
        );
    }

    /**
     * @Then /^counter "([^"]*)" by "([^"]*)" exists$/
     * @Then /^counter "([^"]*)" exists$/
     */
    public function counterDoesExist($counter, $owner = null)
    {
        Assert::true(
            $this->counterStorage->exists($counter, $owner),
            "Counter '$counter' does not exist"
        );
    }

    /**
     * @Then /^json response has message "([^"]*)"$/
     * @Then /^response says "([^"]*)"$/
     */
    public function jsonResponseHasMessage($expectedMessage)
    {
        $response = $this->curl->getJsonResponse();
        if ( ! isset($response['message']))
        {
            throw new Exception('No message in response: ' . json_encode($response));
        }
        Assert::equals(
            $expectedMessage,
            $response['message']
        );
    }

    private function pageMatchesRegexp($regexp, $messageIfNot = null)
    {
        Assert::regexp($regexp, $this->curl->getBody(), $messageIfNot);
    }

    private function pageNotMatchesRegexp($regexp, $messageIfNot = null)
    {
        Assert::notRegexp($regexp, $this->curl->getBody(), $messageIfNot);
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
