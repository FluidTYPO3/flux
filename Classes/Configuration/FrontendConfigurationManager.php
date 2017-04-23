<?php
namespace FluidTYPO3\Flux\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Custom implementation of configuration manager, solely to
 * avoid issue described on https://forge.typo3.org/issues/79098
 * which applies when using content type registrations based
 * on template files.
 */
class FrontendConfigurationManager extends \TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager
{
    /**
     * @return array
     */
    public function getTypoScriptSetup()
    {
        return (array) parent::getTypoScriptSetup();
    }

    /**
     * @return array
     */
    protected function getExtbaseConfiguration()
    {
        static $configuration;
        if (!$configuration) {
            $configuration = parent::getExtbaseConfiguration();
        }
        return $configuration;
    }

}
