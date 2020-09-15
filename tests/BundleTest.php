<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ReadModelsBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Somnambulist\Bundles\ReadModelsBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Components\ReadModels\EventSubscribers\IdentityMapClearerMessengerSubscriber;
use Somnambulist\Components\ReadModels\EventSubscribers\IdentityMapClearerSubscriber;
use Somnambulist\Components\ReadModels\Manager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function array_keys;
use function get_class;
use function is_object;

/**
 * Class BundleTest
 *
 * @package    Somnambulist\Bundles\ReadModelsBundle\Tests
 * @subpackage Somnambulist\Bundles\ReadModelsBundle\Tests\BundleTest
 */
class BundleTest extends KernelTestCase
{

    use BootKernel;

    public function testLoading()
    {
        /** @var Manager $manager */
        $manager = static::$container->get(Manager::class);

        $this->assertTrue($manager->caster()->has('uuid_id'));
        $this->assertTrue($manager->caster()->has('my_service'));

        foreach (array_keys(Type::getTypesMap()) as $type) {
            $this->assertTrue($manager->caster()->has($type));
        }

        $this->assertInstanceOf(Connection::class, $manager->connection()->for());
    }

    public function testBundleConfiguresStaticInstance()
    {
        $manager = Manager::instance();

        $this->assertTrue($manager->caster()->has('uuid_id'));
        $this->assertTrue($manager->caster()->has('my_service'));

    }

    public function testRegistersEventListeners()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = static::$container->get(EventDispatcherInterface::class);

        $toTest = [];
        foreach ($dispatcher->getListeners(KernelEvents::TERMINATE) as $listeners) {
            foreach ($listeners as $listener) {
                if (is_object($listener)) {
                    $toTest[] = get_class($listener);
                }
            }
        }

        $this->assertContains(IdentityMapClearerSubscriber::class, $toTest);
    }

    public function testDoesNotRegisterMessengerListenerWhenMessengerNotInstalled()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = static::$container->get(EventDispatcherInterface::class);

        $toTest = [];
        foreach ($dispatcher->getListeners(WorkerMessageHandledEvent::class) as $listeners) {
            foreach ($listeners as $listener) {
                if (is_object($listener)) {
                    $toTest[] = get_class($listener);
                }
            }
        }

        $this->assertNotContains(IdentityMapClearerMessengerSubscriber::class, $toTest);
    }

    public function testNoConfiguredConnectionsUsesDefault()
    {
        $kernel = self::bootKernel(['environment' => 'demo']);

        $manager = $kernel->getContainer()->get(Manager::class);

        $this->assertInstanceOf(Connection::class, $manager->connection()->for('default'));
    }
}
