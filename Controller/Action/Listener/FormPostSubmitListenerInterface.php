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

use Symfony\Component\Form\Event\PostSubmitEvent;

/**
 * Interface of the single action listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormPostSubmitListenerInterface extends ActionListenerInterface
{
    /**
     * Action on post submit data.
     */
    public function onFormPostSubmit(PostSubmitEvent $event): void;
}
