<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use TYPO3\CMS\Core\TypoScript\TemplateService;

/**
 * @codeCoverageIgnore
 */
class StaticTypoScriptInclusion
{
    protected static bool $recursed = false;
    private SpooledConfigurationApplicator $applicator;

    public function __construct(SpooledConfigurationApplicator $applicator)
    {
        $this->applicator = $applicator;
    }

    public function includeStaticTypoScriptHook(array $parameters, TemplateService $caller): void
    {
        $property = new \ReflectionProperty($caller, 'extensionStaticsProcessed');
        $property->setAccessible(true);
        if (!$property->getValue($caller) && !static::$recursed) {
            // We store a recursion marker here. In some edge cases, the execution flow of parent methods will have set
            // the "extensionStaticsProcessed" property to false before calling this hook method, even though it has
            // already called the method once. Since $this->processData() also attempts to read TypoScript and TYPO3's
            // TemplateService then continuously keeps calling the hook method with "extensionStaticsProcessed" being
            // false, this triggers an infinite recursion. Therefore we record if the hook function has already been
            // called once and if so, we don't allow it to be called a second time (once-only execution is both expected
            // and desired).
            // This prevents a potential infinite recursion started from within TemplateService without causing any
            // negative side effects for non-edge cases. However, it also means that in this particular edge case, the
            // composition of registered Flux content types will not be possible to affect by third-party extension
            // static TS.
            // The only currently known edge case that causes this infinite recursion is when a frontend request is made
            // to a non-existing page that is recorded in the "redirects" module as a redirect. When the redirect has
            // been processed, the request to the target page is not affected by the potential infinite recursion. This
            // means that the negative side effect is only ever relevant at a point where no content rendering can take
            // place - and therefore, the negative impact of this is considered very marginal.
            static::$recursed = true;

            $this->applicator->processData();
        }
    }
}
