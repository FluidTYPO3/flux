<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use TYPO3\CMS\Core\Imaging\GraphicalFunctions;

class DummyGraphicalFunctions extends GraphicalFunctions
{
    public function init()
    {
        return null;
    }

    public function imageMagickConvert(
        $imagefile,
        $newExt = '',
        $w = '',
        $h = '',
        $params = '',
        $frame = '',
        $options = [],
        $mustCreate = false
    ) {
        return ['foobar-0', 'foobar-1', 'foobar-2', 'foobar-3'];
    }
}
