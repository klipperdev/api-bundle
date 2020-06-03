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

use Klipper\Component\DoctrineExtra\Util\ClassUtils;

/**
 * Trait for the action with object.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait ActionWithObjectTrait
{
    use ActionBuilderHandlerTrait;

    private ?object $object = null;

    /**
     * Set the object instance.
     *
     * @param null|object $object The object instance
     *
     * @return static
     */
    public function setObject(?object $object): self
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get the object instance.
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * Get the object classname.
     */
    public function getClass(): string
    {
        return null !== $this->object ? ClassUtils::getClass($this->object) : '';
    }

    /**
     * Build the action.
     *
     * @param string      $formType The form type
     * @param null|object $object   The object
     *
     * @return static
     */
    public static function build(string $formType, ?object $object): self
    {
        $classname = __CLASS__;

        return new $classname($formType, $object);
    }
}
