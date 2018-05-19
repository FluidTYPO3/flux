<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Returns parent uid
 */
class TcaUserfuncs
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FluxService
     */
    protected $fluxService;

    /**
     * @var RecordService
     */
    protected $recordService;

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->fluxService = $this->objectManager->get(FluxService::class);
        $this->recordService = $this->objectManager->get(RecordService::class);
    }

    /**
     * getParent
     *
     * @param array $params
     * @return void
     */

    public function getParent(&$params)
    {
        $rawRecord = $this->recordService->getSingle('tt_content', 'colPos', $params['row']['uid']);
        return (int) ($rawRecord['colPos'] / 100);
    }


}
