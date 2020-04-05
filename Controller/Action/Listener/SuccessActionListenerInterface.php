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

use Klipper\Component\Resource\ResourceInterface;

/**
 * Interface of the success single action listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SuccessActionListenerInterface extends ActionListenerInterface
{
    /**
     * Action on success for the single resource.
     *
     * @param ResourceInterface $result The domain resource
     */
    public function onSingleSuccess(ResourceInterface $result): void;
}
