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
trait ActionWithClassOrObjectTrait
{
    use ActionBuilderHandlerTrait;

    /**
     * @var object|string
     */
    private $object;

    /**
     * Set the object instance will be updated or classname to create new object.
     *
     * @param object|string $object The object instance or the classname
     *
     * @return static
     */
    public function setObject($object): self
    {
        $this->object = \is_object($object) ? $object : (string) $object;

        return $this;
    }

    /**
     * Get the object instance will be updated or classname to create new object.
     *
     * @return object|string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get the object classname.
     */
    public function getClass(): string
    {
        return \is_object($this->object) ? ClassUtils::getClass($this->object) : (string) $this->object;
    }

    /**
     * Build the action.
     *
     * @param string        $formType The form type
     * @param object|string $object   The object instance will be updated or classname to create new object
     *
     * @return static
     */
    public static function build(string $formType, $object): self
    {
        $classname = __CLASS__;

        return new $classname($formType, $object);
    }
}
