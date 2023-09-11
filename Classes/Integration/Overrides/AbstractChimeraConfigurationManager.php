<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\Overrides;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

abstract class AbstractChimeraConfigurationManager extends ConfigurationManager
{
    protected ContainerInterface $container;
    protected FrontendConfigurationManager $frontendConfigurationManager;
    protected BackendConfigurationManager $backendConfigurationManager;
    protected ?ApplicationType $applicationType = null;

    /** @var ServerRequest|ServerRequestInterface|null */
    protected $request = null;

    public function getConfiguration(
        string $configurationType,
        string $extensionName = null,
        string $pluginName = null
    ): array {
        $configurationManager = $this->resolveConfigurationManager();

        switch ($configurationType) {
            case self::CONFIGURATION_TYPE_SETTINGS:
                $configuration = $configurationManager->getConfiguration($extensionName, $pluginName);
                return $configuration['settings'] ?? [];
            case self::CONFIGURATION_TYPE_FRAMEWORK:
                return $configurationManager->getConfiguration($extensionName, $pluginName);
            case self::CONFIGURATION_TYPE_FULL_TYPOSCRIPT:
                return $configurationManager->getTypoScriptSetup();
            default:
                throw new InvalidConfigurationTypeException(
                    'Invalid configuration type "' . $configurationType . '"',
                    1206031879
                );
        }
    }

    public function setConfiguration(array $configuration = []): void
    {
        parent::setConfiguration($configuration);
        $this->resolveConfigurationManager()->setConfiguration($configuration);
    }

    public function setContentObject(ContentObjectRenderer $contentObject): void
    {
        parent::setContentObject($contentObject);
        $this->resolveConfigurationManager()->setContentObject($contentObject);
    }

    /**
     * @param ServerRequestInterface|ServerRequest $request
     */
    protected function updateRequest($request): void
    {
        $this->applicationType = ApplicationType::fromRequest($request);
        $this->request = $request;

        if (method_exists(ConfigurationManager::class, 'setRequest')) {
            parent::setRequest($request);
            $this->frontendConfigurationManager->setRequest($request);
            $this->backendConfigurationManager->setRequest($request);
        }
    }

    protected function refreshRequestIfNecessary(): void
    {
        /** @var ServerRequestInterface|ServerRequest|null $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (($request instanceof ServerRequestInterface || $request instanceof ServerRequest)
            && $request !== $this->request
        ) {
            $this->updateRequest($request);
        }
    }

    /**
     * @return BackendConfigurationManager|FrontendConfigurationManager
     */
    private function resolveConfigurationManager()
    {
        $this->refreshRequestIfNecessary();
        if ($this->applicationType instanceof ApplicationType && $this->applicationType->isFrontend()) {
            $this->concreteConfigurationManager = $this->frontendConfigurationManager;
            return $this->frontendConfigurationManager;
        }
        $this->concreteConfigurationManager = $this->backendConfigurationManager;
        return $this->backendConfigurationManager;
    }
}
