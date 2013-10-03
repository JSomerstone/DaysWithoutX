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

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
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
    public function userPostsNewCounter($counterName)
    {
        $post = array(
            'thing' => $counterName
        );
        $this->request = Request::create(
            '/create',
            'POST',
            $post
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
            throw new Exception("Page did not have expected '$expectedString");
        }
    }

    /**
     * @Given /^counter "([^"]*)" with "([^"]*)" days exists$/
     */
    public function counterWithDaysExists($thing, $days)
    {
        $reseted = time() - 60 * 60 * 24 * $days;
        $counterModel = new JSomerstone\DaysWithoutBundle\Model\CounterModel(
            $thing,
            $days,
            date('Y-m-d', $reseted)
        );
        $counterModel->persist('/tmp');
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
}
