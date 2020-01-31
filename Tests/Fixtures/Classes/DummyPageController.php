<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Fluidpages project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\PageController;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Class DummyPageController
 */
class DummyPageController extends PageController
{

    /**
     * @var array
     */
    protected $record = array();

    /**
     * @param ViewInterface $view
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * @return array
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param array $record
     */
    public function setRecord(array $record)
    {
        $this->record = $record;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param ProviderInterface $provider
     * @return void
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }
}
