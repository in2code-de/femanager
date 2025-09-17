<?php

namespace In2code\Femanager\DependencyInjection;

use In2code\Femanager\DependencyInjection\CompilerPass\ChangeClassDatamapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    $containerBuilder->addCompilerPass(new ChangeClassDatamapPass());

    $services = $configurator->services();
    $defaults = $services->defaults();
    $defaults->autoconfigure();
    $defaults->autowire();
    $defaults->private();
};
