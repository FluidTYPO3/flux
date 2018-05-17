<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
{
    /**
     * @var GridProviderInterface
     */
    protected $provider;

    /**
     * @var array
     */
    protected $record;

    public function setProvider(GridProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function setRecord(array $record)
    {
        $this->record = $record;
    }

    protected function initializeDataProviderCollection()
    {
        // This is an override designed to perform no operations except create a valid data provider collection instance
        $this->setDataProviderCollection(GeneralUtility::makeInstance(DataProviderCollection::class));
    }

    public function getSelectedBackendLayout($pageId)
    {
        // Delegate resolving of backend layout structure to the Provider, which will return a Grid, which can create
        // a full backend layout data array.
        $layoutData = $this->provider->getGrid($this->record)->buildExtendedBackendLayoutArray($this->record['uid']);
        $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = array_merge(
            $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'],
            $layoutData['__items']
        );
        return $layoutData;
    }
}
