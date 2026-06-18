<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Page\PageInformation;

class RequestResolver
{
    public static function getRequest(): ServerRequestInterface
    {
        /** @var ServerRequestInterface|null $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request) {
            throw new \UnexpectedValueException('Request cannot be resolved', 1777024778);
        }
        return $request;
    }

    public static function getPageInformation(): PageInformation
    {
        /** @var PageInformation $pageInformation */
        $pageInformation = self::getRequest()->getAttribute('frontend.page.information');
        return $pageInformation;
    }

    public static function getLanguage(): SiteLanguage
    {
        /** @var SiteLanguage $siteLanguage */
        $siteLanguage = self::getRequest()->getAttribute('language');
        return $siteLanguage;
    }

    public static function getFrontendUser(): ?FrontendUserAuthentication
    {
        /** @var FrontendUserAuthentication $frontendUser */
        $frontendUser = self::getRequest()->getAttribute('frontend.user');
        return $frontendUser;
    }

    public static function getBackendUser(): ?BackendUserAuthentication
    {
        /** @var BackendUserAuthentication|null $backendUser */
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        return $backendUser;
    }

    public static function isFrontendUserLoggedIn(): bool
    {
        return self::getFrontendUser() !== null;
    }
}
