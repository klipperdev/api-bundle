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

use Symfony\Component\Form\Event\PreSetDataEvent;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormPreSetDataListenerInterface extends ActionListenerInterface
{
    /**
     * Action on pre set data data.
     */
    public function onFormPreSetData(PreSetDataEvent $event): void;
}
