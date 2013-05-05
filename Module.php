<?php
/*!
 * SimpleGrid - Super Simple Grid for Zend Framework 2.x
 * Copyright(c) 2013 Tom Shaw <tom@tomshaw.info>
 * MIT Licensed
 */
namespace SimpleGrid;

class Module
{
    public function getConfig()
    {
        return array();
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                )
            )
        );
    }
    
    public function getControllerPluginConfig()
    {
        return array(
            'invokables' => array(
                'grid' => 'SimpleGrid\Controller\Plugin\Grid'
            )
        );
    }

}
