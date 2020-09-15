<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ReadModelsBundle;

use Somnambulist\Components\ReadModels\Manager;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SomnambulistReadModelsBundle
 *
 * @package    Somnambulist\Bundles\ReadModelsBundle
 * @subpackage Somnambulist\Bundles\ReadModelsBundle\SomnambulistReadModelsBundle
 */
class SomnambulistReadModelsBundle extends Bundle
{
    public function boot()
    {
        $this->container->get(Manager::class);
    }
}
