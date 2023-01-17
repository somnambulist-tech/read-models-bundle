<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ReadModelsBundle\DependencyInjection;

use Somnambulist\Components\ReadModels\EventSubscribers\IdentityMapClearerMessengerSubscriber;
use Somnambulist\Components\ReadModels\EventSubscribers\IdentityMapClearerSubscriber;
use Somnambulist\Components\ReadModels\Manager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;
use function class_exists;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class SomnambulistReadModelsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (empty($config['connections'])) {
            $config['connections']['default'] = 'doctrine.dbal.default_connection';
        }

        $connections = [];
        foreach ($config['connections'] as $name => $service) {
            $connections[$name] = new Reference($service);
        }

        $manager = $container->getDefinition(Manager::class);
        $manager
            ->setArgument('$connections', $connections)
            ->setArgument('$casters', tagged_iterator('somnambulist.read_models.type_caster'))
        ;

        if (false === $config['subscribers']['request_manager_clearer']) {
            $container->removeDefinition(IdentityMapClearerSubscriber::class);
        }
        if (false === $config['subscribers']['messenger_manager_clearer'] || !class_exists(MessageBusInterface::class)) {
            $container->removeDefinition(IdentityMapClearerMessengerSubscriber::class);
        }
    }
}
