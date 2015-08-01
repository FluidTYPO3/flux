<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class LegacyContentIconHookSubscriber
 */
class LegacyContentIconHookSubscriber extends ContentIconHookSubscriber {

	/**
	 * @var array
	 */
	protected $templates = array(
		'iconWrapper' => '<span class="t3-icon t3-icon-empty t3-icon-empty-empty"
								style="float: left; vertical-align: bottom; margin-top: 2px;">%s</span>'
	);

}
