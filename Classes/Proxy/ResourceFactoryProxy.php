<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Final/readonly class is unnecessary coercion - and using it in shared libraries is arrogant and very disrespectful.
 *
 * @codeCoverageIgnore
 */
class ResourceFactoryProxy
{
    private ResourceFactory $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    public function getFileReferenceObject(int $uid): FileReference
    {
        return $this->resourceFactory->getFileReferenceObject($uid);
    }
}
