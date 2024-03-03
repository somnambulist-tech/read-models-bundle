<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ReadModelsBundle;

use Somnambulist\Components\ReadModels\Manager;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SomnambulistReadModelsBundle extends Bundle
{
    public function boot(): void
    {
        $this->container->get(Manager::class);
    }
}
