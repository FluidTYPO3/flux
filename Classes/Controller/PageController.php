<?php
namespace FluidTYPO3\Flux\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\RequestResolver;
use FluidTYPO3\Flux\Utility\VersionUtility;

class PageController extends AbstractFluxController
{
    protected ?string $fluxRecordField = 'tx_fed_page_flexform';
    protected ?string $fluxTableName = 'pages';

    public function getRecord(): array
    {
        if (!VersionUtility::isCoreAtLeast13()) {
            return parent::getRecord();
        }
        return RequestResolver::getPageInformation()->getPageRecord();
    }
}
