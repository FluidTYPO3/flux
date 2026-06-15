<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ContentTypeBuilder;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

class ModifyLoadedPageTsConfigEventListener
{
    public function __construct(private ContentTypeBuilder $contentTypeBuilder)
    {
    }

    #[AsEventListener('flux-pagetsconfig')]
    public function addPageTsConfigForFluxContent(ModifyLoadedPageTsConfigEvent $event): void
    {
        $tsConfig = $event->getTsConfig();

        /** @var array<ProviderInterface, string> $providers */
        $providers = Core::getRegisteredFlexFormProviders();
        foreach ($providers as $provider) {
            if (!$provider instanceof ContentTypeProviderInterface || !$provider instanceof FormProviderInterface) {
                continue;
            }

            $contentType = $provider->getContentObjectType();

            $form = $provider->getForm(['CType' => $contentType]);
            if (!$form) {
                continue;
            }

            $tsConfig[] = $this->contentTypeBuilder->createPageTsConfig(
                $form,
                $contentType,
                $this->contentTypeBuilder->addIcon($form, $contentType)
            );
        }

        $event->setTsConfig($tsConfig);
    }
}
