<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Form\Wizard\Suggest;

/**
 * SuggestTest
 */
class SuggestTest extends AbstractWizardTest
{

    /**
     * @test
     */
    public function canUseCommaSeparatedStoragePageUids()
    {
        /** @var Suggest $wizard */
        $wizard = $this->createInstance();
        $storagePageUidsCommaSeparated = '1,2,3';
        $storagePageUidsArray = GeneralUtility::trimExplode(',', $storagePageUidsCommaSeparated);
        $wizard->setStoragePageUids($storagePageUidsCommaSeparated);
        $this->assertSame($storagePageUidsArray, $wizard->getStoragePageUids());
    }

    /**
     * @test
     */
    public function canUseArrayStoragePageUids()
    {
        /** @var Suggest $wizard */
        $wizard = $this->createInstance();
        $storagePageUidsCommaSeparated = '1,2,3';
        $storagePageUidsArray = GeneralUtility::trimExplode(',', $storagePageUidsCommaSeparated);
        $wizard->setStoragePageUids($storagePageUidsArray);
        $this->assertSame($storagePageUidsArray, $wizard->getStoragePageUids());
    }
}
