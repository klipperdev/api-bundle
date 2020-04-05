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

use Klipper\Bundle\ApiBundle\Controller\Action\ActionInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\ActionListenerInterface;
use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;

/**
 * Trait for the action with object.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait ActionBuilderHandlerTrait
{
    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * {@inheritdoc}
     *
     * @see ActionInterface::addListener()
     */
    public function addListener($listener, ?string $actionInterface = null): self
    {
        $interfaces = class_implements($listener);

        if (!$listener instanceof ActionListenerInterface && !\is_callable($listener)) {
            throw new InvalidArgumentException('The listener must be an callable or an instance of ActionListenerInterface');
        }

        if (empty($interfaces)) {
            if (null === $actionInterface) {
                throw new InvalidArgumentException('The listener requires the $actionInterface argument if it is not a class');
            }

            $interfaces = [$actionInterface];
        }

        foreach ($interfaces as $interface) {
            $this->listeners[$interface][] = $listener;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see ActionInterface::getListeners()
     */
    public function getListeners(string $interface): iterable
    {
        return $this->listeners[$interface] ?? [];
    }
}
