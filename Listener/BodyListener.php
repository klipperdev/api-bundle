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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Request body listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class BodyListener
{
    /**
     * @var RequestMatcherInterface
     */
    protected $matcher;

    /**
     * Constructor.
     *
     * @param RequestMatcherInterface $matcher The request matcher
     */
    public function __construct(RequestMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * Core request handler.
     *
     * @param RequestEvent $event The event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->isApiForm($request)) {
            $request->headers->set('CONTENT_TYPE', null);
        }
    }

    /**
     * Check if the request is a api request.
     *
     * @param Request $request The request
     */
    protected function isApiForm(Request $request): bool
    {
        return $this->matcher->matches($request)
            && \in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)
            && 'form' === $request->getContentType();
    }
}
