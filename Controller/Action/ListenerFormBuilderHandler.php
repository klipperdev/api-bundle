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

use Klipper\Bundle\ApiBundle\Controller\Action\Listener\FormPostSetDataListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\FormPostSubmitListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\FormPreSetDataListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\FormPreSubmitListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\FormSubmitListenerInterface;
use Klipper\Bundle\ApiBundle\Util\CallableUtil;
use Symfony\Component\Form\Event\PostSetDataEvent;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

/**
 * Form builder handler to call the action listeners.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ListenerFormBuilderHandler
{
    /**
     * @var ActionInterface|ActionListInterface|CommonActionInterface
     */
    private $action;

    /**
     * @param ActionInterface|ActionListInterface|CommonActionInterface $action
     */
    public function __construct(CommonActionInterface $action)
    {
        $this->action = $action;
    }

    public function __invoke(FormBuilderInterface $builder): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event): void {
                foreach ($this->action->getListeners(FormPreSetDataListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onFormPreSetData', [$event]);
                }
            })
            ->addEventListener(FormEvents::POST_SET_DATA, function (PostSetDataEvent $event): void {
                foreach ($this->action->getListeners(FormPostSetDataListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onFormPostSetData', [$event]);
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $event): void {
                foreach ($this->action->getListeners(FormPreSubmitListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onFormPreSubmit', [$event]);
                }
            })
            ->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event): void {
                foreach ($this->action->getListeners(FormSubmitListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onFormSubmit', [$event]);
                }
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (PostSubmitEvent $event): void {
                foreach ($this->action->getListeners(FormPostSubmitListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onFormPostSubmit', [$event]);
                }
            })
        ;
    }
}
