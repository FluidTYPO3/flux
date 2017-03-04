<?php
namespace FluidTYPO3\Flux\Backend\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */


use FluidTYPO3\Flux\Backend\Domain\Repository\LocalizationRepository;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * LocalizationController handles the AJAX requests for record localization
 */
class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{
    /**
     * @var LocalizationRepository
     */
    protected $localizationRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Overwrite the localizationRepository to the flux related one
        $this->localizationRepository = GeneralUtility::makeInstance(CompatibilityRegistry::get(\FluidTYPO3\Flux\Backend\Domain\Repository\LocalizationRepository::class));
    }
}
