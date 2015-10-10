<?php
namespace FluidTYPO3\Flux\Domain\Repository;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * DummyRepository
 */
class DummyRepository extends Repository {

	/**
	 * @param array $identifiers
	 * @return array
	 */
	public function findByIdentifiers($identifiers) {
		return $identifiers;
	}

}
