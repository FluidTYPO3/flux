<?php

namespace FluidTYPO3\Flux;

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Integration\Overrides\ChimeraConfigurationManager;
use FluidTYPO3\Flux\Integration\Overrides\LegacyChimeraConfigurationManager;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $container) {
    $version = VersionNumberUtility::getCurrentTypo3Version();
    $container->removeAlias(ConfigurationManagerInterface::class);
    $aliasClass = version_compare($version, '11.0', '<')
        ? LegacyChimeraConfigurationManager::class
        : ChimeraConfigurationManager::class;
    $container->setAlias(ConfigurationManagerInterface::class, new Alias($aliasClass, true));

    $container->registerAttributeForAutoconfiguration(
        DataTransformer::class,
        static function (ChildDefinition $definition, DataTransformer $attribute): void {
            $definition->addTag(DataTransformer::TAG_NAME, ['identifier' => $attribute->identifier]);
        }
    );
};
