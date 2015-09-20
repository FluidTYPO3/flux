<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use TYPO3\CMS\Core\Imaging\GraphicalFunctions;

class DummyGraphicalFunctions extends GraphicalFunctions {

	public function init() {
		return NULL;
	}

	public function imageMagickConvert(
		$imagefile,
		$newExt = '',
		$w = '',
		$h = '',
		$params = '',
		$frame = '',
		$options = array(),
		$mustCreate = FALSE
	) {
		return array('foobar-0', 'foobar-1', 'foobar-2', 'foobar-3');
	}

}
