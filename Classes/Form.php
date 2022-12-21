<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Sheet;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Outlet\OutletInterface;
use FluidTYPO3\Flux\Outlet\StandardOutlet;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class Form extends Form\AbstractFormContainer implements Form\FieldContainerInterface, Form\OptionCarryingInterface
{
    const OPTION_STATIC = 'static';
    const OPTION_SORTING = 'sorting';
    const OPTION_GROUP = 'group';
    const OPTION_ICON = 'icon';
    const OPTION_TCA_LABELS = 'labels';
    const OPTION_TCA_HIDE = 'hide';
    const OPTION_TCA_START = 'start';
    const OPTION_TCA_END = 'end';
    const OPTION_TCA_DELETE = 'delete';
    const OPTION_TCA_FEGROUP = 'frontendUserGroup';
    const OPTION_TEMPLATEFILE = 'templateFile';
    const OPTION_TEMPLATEFILE_RELATIVE = 'templateFileRelative';
    const OPTION_RECORD = 'record';
    const OPTION_RECORD_FIELD = 'recordField';
    const OPTION_RECORD_TABLE = 'recordTable';
    const OPTION_DEFAULT_VALUES = 'defaultValues';
    const OPTION_TRANSFORM = 'transform';
    const POSITION_TOP = 'top';
    const POSITION_BOTTOM = 'bottom';
    const POSITION_BOTH = 'both';
    const POSITION_NONE = 'none';
    const CONTROL_INFO = 'info';
    const CONTROL_NEW = 'new';
    const CONTROL_DRAGDROP = 'dragdrop';
    const CONTROL_SORT = 'sort';
    const CONTROL_HIDE = 'hide';
    const CONTROL_DELETE = 'delete';
    const CONTROL_LOCALISE = 'localize';
    const DEFAULT_LANGUAGEFILE = '/Resources/Private/Language/locallang.xlf';

    /**
     * Machine-readable, lowerCamelCase ID of this form. DOM compatible.
     */
    protected string $id = '';

    protected ?string $description = null;
    protected array $options = [];
    protected OutletInterface $outlet;

    public function __construct()
    {
        parent::__construct();
        $this->initializeObject();
    }

    public function initializeObject(): void
    {
        /** @var Form\Container\Sheet $defaultSheet */
        $defaultSheet = GeneralUtility::makeInstance(Sheet::class);
        $defaultSheet->setName('options');
        $defaultSheet->setLabel('LLL:EXT:flux' . $this->localLanguageFileRelativePath . ':tt_content.tx_flux_options');
        $this->add($defaultSheet);
        /** @var StandardOutlet $outlet */
        $outlet = GeneralUtility::makeInstance(StandardOutlet::class);
        $this->outlet = $outlet;
    }

    public static function create(array $settings = []): self
    {
        /** @var Form $object */
        $object = GeneralUtility::makeInstance(static::class);
        $object->initializeObject();
        $object->modify($settings);
        return HookHandler::trigger(HookHandler::FORM_CREATED, ['form' => $object])['form'];
    }

    public function add(Form\FormInterface $child): self
    {
        if (false === $child instanceof Form\Container\Sheet) {
            /** @var Form\Container\Sheet $last */
            $last = $this->last();
            $last->add($child);
        } else {
            $this->children->rewind();
            /** @var FormInterface|null $firstChild */
            $firstChild = $this->children->count() > 0 ? $this->children->current() : null;
            if ($firstChild instanceof FormInterface
                && $this->children->count() === 1
                && $firstChild->getName() === 'options'
                && !$firstChild->hasChildren()
            ) {
                // Form has a single sheet, it's the default sheet and it has no fields. Replace it.
                $this->children->detach($this->children->current());
            }
            foreach ($this->children as $existingChild) {
                if ($child->getName() === $existingChild->getName()) {
                    return $this;
                }
            }
            $this->children->attach($child);
            $child->setParent($this);
        }
        HookHandler::trigger(HookHandler::FORM_CHILD_ADDED, ['parent' => $this, 'child' => $child]);
        return $this;
    }

    public function build(): array
    {
        $disableLocalisation = 1;
        $inheritLocalisation = 0;
        $dataStructArray = [
            'meta' => [
                'langDisable' => $disableLocalisation,
                'langChildren' => $inheritLocalisation
            ],
        ];
        $sheets = $this->getSheets(false);
        if (count((array) $sheets) > 0) {
            $dataStructArray['sheets'] = $this->buildChildren($sheets);
        } else {
            $dataStructArray['ROOT'] = [
                'type' => 'array',
                'el' => []
            ];
        }
        return HookHandler::trigger(HookHandler::FORM_BUILT, ['dataStructure' => $dataStructArray])['dataStructure'];
    }

    /**
     * @return Sheet[]|FormInterface[]
     */
    public function getSheets(bool $includeEmpty = false): iterable
    {
        $sheets = [];
        foreach ($this->children as $sheet) {
            if (false === $sheet->hasChildren() && false === $includeEmpty) {
                continue;
            }
            $name = $sheet->getName();
            $sheets[$name] = $sheet;
        }
        return $sheets;
    }

    /**
     * @return Form\FieldInterface[]
     */
    public function getFields(): iterable
    {
        /** @var Sheet[] $sheets */
        $sheets = $this->getSheets();
        /** @var FieldInterface[] $fields */
        $fields = [];
        foreach ($sheets as $sheet) {
            $fieldsInSheet = (array) $sheet->getFields();
            /** @var FieldInterface[] $fields */
            $fields = array_merge($fields, $fieldsInSheet);
        }
        return $fields;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        if (true === empty($this->name)) {
            $this->name = $id;
        }
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        $description = $this->description;
        $translated = null;
        $extensionKey = ExtensionNamingUtility::getExtensionKey((string) $this->extensionName);
        if (empty($description)) {
            $relativeFilePath = $this->getLocalLanguageFileRelativePath();
            $relativeFilePath = ltrim($relativeFilePath, '/');
            $filePrefix = 'LLL:EXT:' . $extensionKey . '/' . $relativeFilePath;
            $description = $filePrefix . ':' . trim('flux.' . $this->id . '.description');
        }
        return $description;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $name, $value): self
    {
        if (strpos($name, '.') === false) {
            $this->options[$name] = $value;
        } else {
            $subject = &$this->options;
            $segments = explode('.', $name);
            while ($segment = array_shift($segments)) {
                if (isset($subject[$segment])) {
                    $subject = &$subject[$segment];
                } else {
                    $subject[$segment] = [];
                    $subject = &$subject[$segment];
                }
            }
            $subject = $value;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOption(string $name)
    {
        return ObjectAccess::getPropertyPath($this->options, $name);
    }

    public function hasOption(string $name): bool
    {
        return true === isset($this->options[$name]);
    }

    public function hasChildren(): bool
    {
        foreach ($this->children as $child) {
            if (true === $child->hasChildren()) {
                return true;
            }
        }
        return false;
    }

    public function setOutlet(OutletInterface $outlet): self
    {
        $this->outlet = $outlet;
        return $this;
    }

    public function getOutlet(): OutletInterface
    {
        return $this->outlet;
    }

    public function modify(array $structure): self
    {
        if (isset($structure['options']) && is_array($structure['options'])) {
            foreach ($structure['options'] as $name => $value) {
                $this->setOption($name, $value);
            }
            unset($structure['options']);
        }
        if (isset($structure['sheets']) || isset($structure['children'])) {
            $data = $structure['sheets'] ?? $structure['children'] ?? [];
            foreach ($data as $index => $sheetData) {
                $sheetName = $sheetData['name'] ?? $index;
                // check if field already exists - if it does, modify it. If it does not, create it.
                if (true === $this->has($sheetName)) {
                    /** @var Sheet $sheet */
                    $sheet = $this->get($sheetName);
                } else {
                    /** @var Sheet $sheet */
                    $sheet = $this->createContainer(Sheet::class, $sheetName);
                }
                $sheet->modify($sheetData);
            }
            unset($structure['sheets'], $structure['children']);
        }
        if (isset($structure['outlet'])) {
            // @TODO: enable modify() on outlet instead of only allowing creation
            $outlet = StandardOutlet::create($structure['outlet']);
            $this->setOutlet($outlet);
            unset($structure['outlet']);
        }

        /** @var self $fromParentMethodCall */
        $fromParentMethodCall = parent::modify($structure);

        return $fromParentMethodCall;
    }
}
