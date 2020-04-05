<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action\Listener;

use Klipper\Component\Resource\ResourceListInterface;

/**
 * Interface of the error list action listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ErrorListActionListenerInterface extends ActionListenerInterface
{
    /**
     * Action on error for the list resource.
     *
     * @param ResourceListInterface $result The domain resource
     */
    public function onListError(ResourceListInterface $result): void;
}
