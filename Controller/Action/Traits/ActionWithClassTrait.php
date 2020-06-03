<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action\Traits;

/**
 * Trait for the action with classname.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait ActionWithClassTrait
{
    use ActionBuilderHandlerTrait;

    private string $class;

    /**
     * Set the object classname.
     *
     * @param string $class The object classname
     *
     * @return static
     */
    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the object classname.
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Build the action.
     *
     * @param string $formType The form type
     * @param string $class    The object classname
     *
     * @return static
     */
    public static function build(string $formType, string $class): self
    {
        $classname = __CLASS__;

        return new $classname($formType, $class);
    }
}
