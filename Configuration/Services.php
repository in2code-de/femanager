<?php

namespace In2code\Femanager\DependencyInjection;

use In2code\Femanager\DependencyInjection\CompilerPass\ChangeClassDatamapPass;
use In2code\Femanager\UserFunc\StaticInfoTables;
use SJBR\StaticInfoTables\Domain\Model\Country;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\DependencyInjection\PublicServicePass;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    $containerBuilder->addCompilerPass(new ChangeClassDatamapPass());

    $services = $configurator->services();
    $defaults = $services->defaults();
    $defaults->autoconfigure();
    $defaults->autowire();
    $defaults->private();

    if (class_exists(Country::class)) {
        $containerBuilder->registerForAutoconfiguration(StaticInfoTables::class)->addTag('femanager.userfunc.staticinfotables');
        $containerBuilder->addCompilerPass(new PublicServicePass('femanager.userfunc.staticinfotables'));

        $services->set(StaticInfoTables::class)
            ->public();
    }
};
