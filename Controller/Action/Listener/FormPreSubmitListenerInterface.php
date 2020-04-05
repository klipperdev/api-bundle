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

use Symfony\Component\Form\Event\PreSubmitEvent;

/**
 * Interface of the single action listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormPreSubmitListenerInterface extends ActionListenerInterface
{
    /**
     * Action on pre submit data.
     */
    public function onFormPreSubmit(PreSubmitEvent $event): void;
}
