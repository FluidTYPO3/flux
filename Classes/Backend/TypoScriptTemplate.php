<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TypoScriptTemplate
 * @deprecated To be removed in next major release
 */
class TypoScriptTemplate
{

    /**
     * Includes static template from extensions
     *
     * @param array $params
     * @param TemplateService $pObj
     * @return void
     */
    public function preprocessIncludeStaticTypoScriptSources(array &$params, TemplateService $pObj)
    {
        unset($pObj);
        if (true === isset($params['row']['root']) && true === (boolean) $params['row']['root']) {
            $existingTemplates = GeneralUtility::trimExplode(',', $params['row']['include_static_file']);
            $globalStaticTemplates = Core::getStaticTypoScript();
            $staticTemplates = array_merge($globalStaticTemplates, $existingTemplates);
            $params['row']['include_static_file'] = implode(',', array_unique($staticTemplates));
        }
    }
}
