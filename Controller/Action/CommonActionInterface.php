<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action;

use Klipper\Bundle\ApiBundle\Controller\Action\Listener\ActionListenerInterface;

/**
 * Interface of common action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface CommonActionInterface
{
    /**
     * Get the object classname.
     */
    public function getClass(): string;

    /**
     * Add the action listener.
     *
     * @param ActionListenerInterface|callable $listener        The action listener
     * @param null|string                      $actionInterface The interface of the action if the callable isn't a class
     *
     * @return static
     */
    public function addListener($listener, ?string $actionInterface = null);

    /**
     * Get all action listeners.
     *
     * @param string $interface The interface
     *
     * @return ActionListenerInterface[]|callable[]
     */
    public function getListeners(string $interface): iterable;
}
