<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Outlet Form Renderer
 *
 * Specialised version of `f:form` which adds three vital behaviors:
 *
 * - Automatic resolving of the correct extension name and plugin name
 * - Automatic use of "outletAction" on controller
 * - Addition of table name and UID as to prevent calling "outletAction"
 *   on any other instance than the one which rendered the form.
 *
 * Together these specialised behaviors mean that the form data will
 * only be processed by the exact instance from which the form was
 * rendered, and will always target the correct plugin namespace for
 * the arguments to be recognised.
 *
 * To customise handling of this form, add an "outletAction" to your
 * Flux controller with which your template is associated, e.g.
 * your "ContentController", "PageController" etc.
 */
class FormViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper
{
    protected ?ProviderInterface $provider = null;
    protected array $record = [];

    public function render(): string
    {
        /** @var ProviderInterface|null $provider */
        $provider = $this->viewHelperVariableContainer->get(self::class, 'provider');
        if (!$provider instanceof ProviderInterface) {
            throw new Exception(
                'Provider associated with outlet must be instance of ' . ProviderInterface::class,
                1669647845
            );
        }

        /** @var array|null $record */
        $record = $this->viewHelperVariableContainer->get(self::class, 'record');
        if (!is_array($record)) {
            throw new Exception('Record not found in context of outlet form', 1669647846);
        }

        $this->provider = $provider;
        $this->record = $record;

        if (!$this->hasArgument('extensionName')) {
            $this->arguments['extensionName'] = ExtensionNamingUtility::getExtensionName(
                $this->provider->getControllerExtensionKeyFromRecord($this->record)
            );
        }

        if (!$this->hasArgument('controller')) {
            $this->arguments['controller'] = $this->provider->getControllerNameFromRecord($this->record);
        }

        if (!$this->hasArgument('pluginName')) {
            $this->arguments['pluginName'] = $this->viewHelperVariableContainer->get(self::class, 'pluginName');
        }

        if (!$this->hasArgument('action')) {
            $this->arguments['action'] = 'outlet';
        }

        return parent::render();
    }

    /**
     * Render additional identity fields which were registered by form elements.
     * This happens if a form field is defined like property="bla.blubb" - then we might
     * need an identity property for the sub-object "bla".
     */
    protected function renderAdditionalIdentityFields(): string
    {
        /** @var ProviderInterface $provider */
        $provider = $this->provider;
        $output = parent::renderAdditionalIdentityFields();
        $output .= '<input type="hidden" name="'
            . $this->prefixFieldName('__outlet[table]')
            . '" value="'
            . $provider->getTableName($this->record)
            . '" />'
            . PHP_EOL
            . '<input type="hidden" name="'
            . $this->prefixFieldName('__outlet[recordUid]')
            . '" value="'
            . $this->record['uid']
            . '" />'
            . PHP_EOL;
        return $output;
    }
}
