<?php
namespace JSomerstone\DaysWithout\Service;

use JSomerstone\DaysWithout\Controller\ContextInterface;

class ContextService
{
    private $registry;

    /**
     * @param array $objectCollection Array of Classes implementing ContextInterface
     */
    public function __construct(array $objectCollection = array())
    {
        foreach ($objectCollection as $controller)
        {
            $this->register($controller);
        }
    }

    public function register(ContextInterface $object)
    {
        $this->registry[$object->getRegisterName()] = $object;
        $object->register($this);
    }

    /**
     * @param string $name
     * @param mixed $object
     */
    public function registerAs($name, $object)
    {
        $this->registry[$name] = $object;
    }

    /**
     * @param string $name
     * @return ControllerInterface $controller
     * @throws \Exception if given controller is not found
     */
    public function get($name)
    {
        if ( ! isset($this->registry[$name]))
        {
            throw new \Exception("No item registered as '$name'");
        }
        return $this->registry[$name];
    }
}
