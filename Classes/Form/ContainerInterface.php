<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * ContainerInterface
 */
interface ContainerInterface extends FormInterface
{

    /**
     * @param string $childName
     * @param boolean $recursive
     * @param string $requiredClass
     * @return FormInterface|FALSE
     */
    public function get($childName, $recursive = false, $requiredClass = null);

    /**
     * @param FormInterface $child
     * @return FormInterface
     */
    public function add(FormInterface $child);

    /**
     * @param mixed $childOrChildName
     * @return boolean
     */
    public function has($childOrChildName);

    /**
     * @param string $childName
     * @return FormInterface|FALSE
     */
    public function remove($childName);

    /**
     * @param string $transform
     * @return ContainerInterface
     */
    public function setTransform($transform);

    /**
     * @return string
     */
    public function getTransform();
}
