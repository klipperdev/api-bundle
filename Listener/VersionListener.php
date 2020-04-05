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

use Klipper\Bundle\ApiBundle\Version\Resolver\VersionResolverInterface;
use Klipper\Bundle\ApiBundle\View\ConfigurableViewHandlerInterface;
use Klipper\Bundle\ApiBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Request version listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class VersionListener
{
    /**
     * @var RequestMatcherInterface
     */
    private $matcher;

    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var VersionResolverInterface
     */
    private $versionResolver;

    /**
     * @var null|string
     */
    private $defaultVersion;

    /**
     * Constructor.
     *
     * @param RequestMatcherInterface  $matcher         The request matcher
     * @param ViewHandlerInterface     $viewHandler     The view handler
     * @param VersionResolverInterface $versionResolver The version resolver
     * @param null|string              $defaultVersion  The default version
     */
    public function __construct(
        RequestMatcherInterface $matcher,
        ViewHandlerInterface $viewHandler,
        VersionResolverInterface $versionResolver,
        ?string $defaultVersion = null
    ) {
        $this->matcher = $matcher;
        $this->viewHandler = $viewHandler;
        $this->versionResolver = $versionResolver;
        $this->defaultVersion = $defaultVersion;
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

        $version = $this->versionResolver->resolve($request);

        if (false === $version && $attr->has('version')) {
            $version = $attr->get('version');
        } elseif (false === $version && null !== $this->defaultVersion) {
            $version = $this->defaultVersion;
        }

        if (false === $version) {
            return;
        }

        $request->attributes->set('version', $version);

        if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
            $this->viewHandler->setExclusionStrategyVersion($version);
        }
    }
}
