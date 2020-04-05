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

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Request version listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AvailableVersionsListener
{
    /**
     * @var RequestMatcherInterface
     */
    protected $matcher;

    /**
     * @var array
     */
    protected $availableVersions;

    /**
     * Constructor.
     *
     * @param RequestMatcherInterface $matcher           The request matcher
     * @param array                   $availableVersions The available versions
     */
    public function __construct(
        RequestMatcherInterface $matcher,
        array $availableVersions = []
    ) {
        $this->matcher = $matcher;
        $this->availableVersions = $availableVersions;
    }

    /**
     * Core request handler.
     *
     * @param RequestEvent $event The event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $attr = $request->attributes;

        if (!$this->matcher->matches($request)) {
            return;
        }

        if ($attr->has('version') && !\in_array($attr->get('version'), $this->availableVersions, true)) {
            $msg = 'The api version "%s" is not available';

            throw new BadRequestHttpException(sprintf($msg, $attr->get('version')));
        }
    }
}
