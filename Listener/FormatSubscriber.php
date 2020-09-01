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

use JMS\Serializer\Exception\UnsupportedFormatException;
use Klipper\Bundle\ApiBundle\Model\ErrorView;
use Klipper\Bundle\ApiBundle\Serializer\Context;
use Klipper\Bundle\ApiBundle\Serializer\SerializerInterface;
use Klipper\Bundle\ApiBundle\View\View;
use Klipper\Bundle\ApiBundle\View\ViewHandlerInterface;
use Klipper\Component\Translation\ExceptionMessageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response format subscriber.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormatSubscriber implements EventSubscriberInterface
{
    private RequestMatcherInterface $matcher;

    private ViewHandlerInterface $viewHandler;

    private ExceptionMessageManager $exceptionMessageManager;

    private SerializerInterface $serializer;

    private string $defaultTypeMime;

    private bool $throwUnsupportedTypeMime;

    private bool $debug;

    /**
     * @param RequestMatcherInterface $matcher                  The request matcher
     * @param ViewHandlerInterface    $viewHandler              The view handler
     * @param ExceptionMessageManager $exceptionMessageManager  The exception message manager
     * @param SerializerInterface     $serializer               The serializer
     * @param string                  $defaultTypeMime          The default type mime
     * @param bool                    $throwUnsupportedTypeMime Check if an exception must be thrown if type mime is not supported
     * @param bool                    $debug                    The debug mode
     */
    public function __construct(
        RequestMatcherInterface $matcher,
        ViewHandlerInterface $viewHandler,
        ExceptionMessageManager $exceptionMessageManager,
        SerializerInterface $serializer,
        string $defaultTypeMime,
        bool $throwUnsupportedTypeMime,
        bool $debug = false
    ) {
        $this->matcher = $matcher;
        $this->viewHandler = $viewHandler;
        $this->exceptionMessageManager = $exceptionMessageManager;
        $this->serializer = $serializer;
        $this->defaultTypeMime = $defaultTypeMime;
        $this->throwUnsupportedTypeMime = $throwUnsupportedTypeMime;
        $this->debug = $debug;
    }

    public static function getSubscribedEvents(): iterable
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 1024],
            ],
            KernelEvents::EXCEPTION => [
                ['onKernelException', 10],
            ],
        ];
    }

    /**
     * Add the good response format for the api.
     *
     * @param RequestEvent $event The event
     *
     * @throws UnsupportedFormatException When the type mime isn't supported
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->matcher->matches($request)) {
            return;
        }

        $format = $this->getFormat($request);

        if (null === $format) {
            if ($this->throwUnsupportedTypeMime) {
                throw new UnsupportedFormatException('The type mime isn\'t supported');
            }

            $format = $request->getFormat($this->defaultTypeMime);
        }

        $request->setRequestFormat($format);
    }

    /**
     * Add the good response format for the api exception.
     *
     * @param ExceptionEvent $event The event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (!$this->matcher->matches($request)) {
            return;
        }

        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        } else {
            $code = $this->exceptionMessageManager->getStatusCode($exception);
        }

        if ($exception instanceof UnsupportedFormatException) {
            $request->setRequestFormat($request->getFormat($this->defaultTypeMime));
        }

        $data = new ErrorView(
            $code,
            $this->exceptionMessageManager->getMessage($exception, Response::$statusTexts[$code]),
            $this->debug ? $exception : null
        );

        $view = View::create($data, $code);
        $response = $this->viewHandler->handle($view, $request);
        $event->setResponse($response);
    }

    /**
     * Get the request format.
     *
     * @param Request $request The request
     */
    protected function getFormat(Request $request): ?string
    {
        $typeMime = $request->headers->get('accept', $this->defaultTypeMime);
        $typeMime = str_replace('*/*', $this->defaultTypeMime, $typeMime);
        $typeMimes = explode(',', $typeMime);
        $format = null;

        if (\count($typeMimes) > 1) {
            foreach ($typeMimes as $acceptTypeMime) {
                try {
                    $format = $request->getFormat($acceptTypeMime);
                    $this->serializer->serialize([], $format, new Context());
                } catch (\Throwable $e) {
                    $format = null;
                }

                if (null !== $format) {
                    break;
                }
            }
        } else {
            $format = $request->getFormat($typeMime);
        }

        return $format;
    }
}
