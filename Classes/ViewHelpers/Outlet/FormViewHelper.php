<?php
namespace FluidTYPO3\Flux\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;

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

    /**
     * @var AbstractProvider
     */
    protected $provider;

    /**
     * @var array
     */
    protected $record;

    /**
     * @return void
     */
    public function render()
    {
        $this->provider = $this->viewHelperVariableContainer->get(\FluidTYPO3\Flux\ViewHelpers\FormViewHelper::class, 'provider');
        $this->record = $this->viewHelperVariableContainer->get(\FluidTYPO3\Flux\ViewHelpers\FormViewHelper::class, 'record');

        if (!$this->hasArgument('extensionName')) {
            $this->arguments['extensionName'] = ExtensionNamingUtility::getExtensionName($this->provider->getControllerExtensionKeyFromRecord($this->record));
        }

        if (!$this->hasArgument('controller')) {
            $this->arguments['controller'] = $this->provider->getControllerNameFromRecord($this->record);
        }

        if (!$this->hasArgument('pluginName')) {
            $this->arguments['pluginName'] = $this->viewHelperVariableContainer->get(\FluidTYPO3\Flux\ViewHelpers\FormViewHelper::class, 'pluginName');
        }

        if (!$this->hasArgument('action')) {
            $this->arguments['action'] = 'outlet';
        }

        return parent::render();
    }

    /**
     * Render additional identity fields which were registered by form elements.
     * This happens if a form field is defined like property="bla.blubb" - then we might need an identity property for the sub-object "bla".
     *
     * @return string HTML-string for the additional identity properties
     */
    protected function renderAdditionalIdentityFields()
    {
        $output = parent::renderAdditionalIdentityFields();
        $output .= '<input type="hidden" name="' . $this->prefixFieldName('__outlet[table]') . '" value="' . $this->provider->getTableName($this->record) . '" />' . LF;
        $output .= '<input type="hidden" name="' . $this->prefixFieldName('__outlet[recordUid]') . '" value="' . $this->record['uid'] . '" />' . LF;
        return $output;
    }
}
