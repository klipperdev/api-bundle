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

use Symfony\Component\Form\Event\SubmitEvent;

/**
 * Interface of the single action listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormSubmitListenerInterface extends ActionListenerInterface
{
    /**
     * Action on submit data.
     */
    public function onFormSubmit(SubmitEvent $event): void;
}
