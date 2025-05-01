<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\WizardItemsManipulator;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;

/**
 * @codeCoverageIgnore
 */
class WizardItems implements NewContentElementWizardHookInterface
{
    protected WizardItemsManipulator $wizardItemsManipulator;
    protected array $requestArguments;

    public function __construct(WizardItemsManipulator $wizardItemsManipulator)
    {
        $this->wizardItemsManipulator = $wizardItemsManipulator;

        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $this->requestArguments = $request->getQueryParams() ?? [];
    }

    /**
     * @param array $items
     * @param NewContentElementController $parentObject
     */
    public function manipulateWizardItems(&$items, &$parentObject): void
    {
        $defaultValues = $this->requestArguments['defVals'] ?? [];

        /** @var array $dataArray */
        $dataArray = $defaultValues['tt_content'] ?? [];
        $pageUidFromUrl = $this->requestArguments['id'] ?? null;
        $pageUidFromUrl = is_scalar($pageUidFromUrl) ? (int) $pageUidFromUrl : null;
        $pageUidFromDataArray = key($dataArray) ?: null;

        $pageUid = (int) ($pageUidFromDataArray ?? $pageUidFromUrl ?? 0);

        if ($pageUid === 0) {
            $reflectionProperty = new \ReflectionProperty($parentObject, 'id');
            $reflectionProperty->setAccessible(true);
            $pageUidFroimParentObject = $reflectionProperty->getValue($parentObject);
            $pageUid = is_scalar($pageUidFroimParentObject) ? (int) $pageUidFroimParentObject : 0;
        }

        /** @var int|null $colPos */
        $colPos = $this->requestArguments['colPos'] ?? null;
        if ($colPos === null) {
            $reflectionProperty = new \ReflectionProperty($parentObject, 'colPos');
            $reflectionProperty->setAccessible(true);
            $colPosFromParentObject = $reflectionProperty->getValue($parentObject);
            $colPos = is_scalar($colPosFromParentObject) ? (int) $colPosFromParentObject : 0;
        }

        $items = $this->wizardItemsManipulator->manipulateWizardItems($items, $pageUid, $colPos);
    }
}
