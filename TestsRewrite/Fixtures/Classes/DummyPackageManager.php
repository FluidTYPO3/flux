<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use TYPO3\CMS\Core\Package\FailsafePackageManager;
use TYPO3\Flow\Core\Bootstrap;

/**
 * Class DummyPackageManager
 */
class DummyPackageManager extends FailsafePackageManager {

	/**
	 * @param Bootstrap $bootstrap
	 */
	public function initialize(Bootstrap $bootstrap) {
		$this->bootstrap = $bootstrap;
	}

	/**
	 * @param string $packageKey
	 * @return boolean
	 */
	public function isPackageActive($packageKey) {
		return 'flux' === $packageKey;
	}

	/**
	 * @param string $packageKey
	 * @return boolean
	 */
	public function isPackageAvailable($packageKey) {
		return 'flux' === $packageKey;
	}

}
