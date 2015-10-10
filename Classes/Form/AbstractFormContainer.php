<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * AbstractFormContainer
 */
abstract class AbstractFormContainer extends AbstractFormComponent implements ContainerInterface {

	/**
	 * @var \SplObjectStorage
	 */
	protected $children;

	/**
	 * @var string
	 */
	protected $transform;

	/**
	 * @var boolean
	 */
	protected $inherit = TRUE;

	/**
	 * @var boolean
	 */
	protected $inheritEmpty = FALSE;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->children = new \SplObjectStorage();
	}

	/**
	 * @param string $namespace
	 * @param string $type
	 * @param string $name
	 * @param null $label
	 * @return FormInterface
	 */
	public function createComponent($namespace, $type, $name, $label = NULL) {
		$component = parent::createComponent($namespace, $type, $name, $label);
		$this->add($component);
		return $component;
	}

	/**
	 * @param FormInterface $child
	 * @return FormInterface
	 */
	public function add(FormInterface $child) {
		if (FALSE === $this->children->contains($child)) {
			$this->children->attach($child);
			$child->setParent($this);
		}
		return $this;
	}

	/**
	 * @param array|\Traversable $children
	 * @return FormInterface
	 */
	public function addAll($children) {
		foreach ($children as $child) {
			$this->add($child);
		}
		return $this;
	}

	/**
	 * @param FieldInterface|string $childName
	 * @return FormInterface|FALSE
	 */
	public function remove($childName) {
		foreach ($this->children as $child) {
			/** @var FieldInterface $child */
			$isMatchingInstance = (TRUE === $childName instanceof FormInterface && $childName->getName() === $child->getName());
			$isMatchingName = ($childName === $child->getName());
			if (TRUE === $isMatchingName || TRUE === $isMatchingInstance) {
				$this->children->detach($child);
				$this->children->rewind();
				$child->setParent(NULL);
				return $child;
			}
		}
		return FALSE;
	}

	/**
	 * @param mixed $childOrChildName
	 * @return boolean
	 */
	public function has($childOrChildName) {
		$name = (TRUE === $childOrChildName instanceof FormInterface) ? $childOrChildName->getName() : $childOrChildName;
		return (FALSE !== $this->get($name));
	}

	/**
	 * @param string $childName
	 * @param boolean $recursive
	 * @param string $requiredClass
	 * @return FormInterface|FALSE
	 */
	public function get($childName, $recursive = FALSE, $requiredClass = NULL) {
		foreach ($this->children as $existingChild) {
			if ($childName === $existingChild->getName() && ($requiredClass === NULL || TRUE === $existingChild instanceof $requiredClass)) {
				return $existingChild;
			}
			if (TRUE === $recursive && TRUE === $existingChild instanceof ContainerInterface) {
				$candidate = $existingChild->get($childName, $recursive);
				if (FALSE !== $candidate) {
					return $candidate;
				}
			}
		}
		return FALSE;
	}

	/**
	 * @return FormInterface|FALSE
	 */
	public function last() {
		$result = array_pop(iterator_to_array($this->children));
		return $result;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return 0 < $this->children->count();
	}

	/**
	 * @param string $transform
	 * @return ContainerInterface
	 */
	public function setTransform($transform) {
		$this->transform = $transform;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTransform() {
		return $this->transform;
	}

	/**
	 * @param array $structure
	 * @return ContainerInterface
	 */
	public function modify(array $structure) {
		if (TRUE === isset($structure['fields'])) {
			foreach ((array) $structure['fields'] as $index => $fieldData) {
				$fieldName = TRUE === isset($fieldData['name']) ? $fieldData['name'] : $index;
				// check if field already exists - if it does, modify it. If it does not, create it.
				if (TRUE === $this->has($fieldName)) {
					$field = $this->get($fieldName);
				} else {
					$fieldType = TRUE === isset($fieldData['type']) ? $fieldData['type'] : 'None';
					$field = $this->createField($fieldType, $fieldName);
				}
				$field->modify($fieldData);
			}
		}
		return parent::modify($structure);
	}

}
