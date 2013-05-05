<?php
/*!
 * SimpleGrid - Super Simple Grid for Zend Framework 2.x
 * Copyright(c) 2013 Tom Shaw <tom@tomshaw.info>
 * MIT Licensed
 */
/**
 * @todo Add security token to form.
 * @see https://github.com/zendframework/zf2/issues/2887
 */
namespace SimpleGrid\Controller\Plugin;

use Zend\Mvc\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use Zend\Session\Container;
use Zend\Session\ManagerInterface as Manager;

class Grid extends AbstractPlugin
{
    protected $namespace;

    protected $container;

    protected $session;

    public function __invoke($namespace)
    {
        $this->setNamespace($namespace);

        $controller = $this->getController();
        $request = $controller->getRequest();

        $params = $controller->params()->fromRoute();

        $container = $this->getContainer();

        if ($request->isPost()) {
            $container->offsetSet('data', array_merge($params, $request->getPost()->toArray()));
        } else {
            if ($container->offsetGet('data')) {
                $container->offsetSet('data', array_merge($container->offsetGet('data'), $params));
            } else {
                $container->offsetSet('data', $params);
            }
        }

        return $container->offsetGet('data');
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setSessionManager(Manager $manager)
    {
        $this->session = $manager;
        return $this;
    }

    public function getSessionManager()
    {
        if (!$this->session instanceof Manager) {
            $this->setSessionManager(Container::getDefaultManager());
        }

        return $this->session;
    }

    public function getContainer()
    {
        if ($this->container instanceof Container) {
            return $this->container;
        }

        $manager = $this->getSessionManager();
        $this->container = new Container($this->getNamespace(), $manager);

        return $this->container;
    }

}