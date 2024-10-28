<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentObjectFetcher
{
    public static function resolve(?ConfigurationManagerInterface $configurationManager = null): ?ContentObjectRenderer
    {
        $contentObject = null;
        $request = $configurationManager !== null && method_exists($configurationManager, 'getRequest')
            ? $configurationManager->getRequest()
            : ($GLOBALS['TYPO3_REQUEST'] ?? null);

        if ($request && $configurationManager === null) {
            $contentObject = static::resolveFromRequest($request);
        }

        if ($contentObject === null) {
            if ($configurationManager !== null && method_exists($configurationManager, 'getContentObject')) {
                $contentObject = $configurationManager->getContentObject();
            } else {
                $contentObject = static::resolveFromRequest($request);
            }
        }

        return $contentObject;
    }

    protected static function resolveFromRequest(ServerRequest $request): ?ContentObjectRenderer
    {
        /** @var TypoScriptFrontendController $controller */
        $controller = $request->getAttribute('frontend.controller');
        return $controller instanceof TypoScriptFrontendController ? $controller->cObj : null;
    }
}
