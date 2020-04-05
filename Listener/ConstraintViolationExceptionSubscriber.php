<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Listener;

use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConstraintViolationExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): iterable
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', -90],
            ],
        ];
    }

    /**
     * Format the constraint violation exception.
     *
     * @param ExceptionEvent $event The event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getException();

        if (!$e instanceof ConstraintViolationException || 0 === $e->getConstraintViolations()->count()) {
            return;
        }

        $message = $e->getConstraintViolations()->get(0)->getMessage();
        $event->setException(new UnprocessableEntityHttpException($message, $e));
    }
}
