<?php
/**
 * Created by PhpStorm.
 * User: bjd
 * Date: 3/20/15
 * Time: 3:26 PM.
 */

namespace Caxy\AppEngine\Bridge\Security\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class Factory
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.app_engine.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('app_engine.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.app_engine.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('app_engine.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'app_engine';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
