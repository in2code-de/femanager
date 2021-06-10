<?php

declare(strict_types = 1);

namespace In2code\Femanager\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;

class ChangeClassDatamapPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(DataMap::class)) {
            return;
        }

        $definition = $container->findDefinition(DataMap::class);
        $definition->setClass(\In2code\Femanager\Persistence\Generic\Mapper\DataMap::class);
    }
}
