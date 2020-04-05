<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\View;

use Klipper\Bundle\ApiBundle\Serializer\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default View implementation.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class View
{
    /**
     * @var null|mixed
     */
    private $data;

    /**
     * @var null|int
     */
    private $statusCode;

    /**
     * @var null|string
     */
    private $format;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Response
     */
    private $response;

    /**
     * Constructor.
     *
     * @param mixed    $data       The data
     * @param null|int $statusCode The status code
     * @param array    $headers    The response headers
     */
    public function __construct($data = null, ?int $statusCode = null, array $headers = [])
    {
        $this->setData($data);
        $this->setStatusCode($statusCode);

        if (!empty($headers)) {
            $this->setHeaders($headers);
        }
    }

    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param int   $statusCode
     *
     * @return static
     */
    public static function create($data = null, ?int $statusCode = null, array $headers = []): self
    {
        return new static($data, $statusCode, $headers);
    }

    /**
     * Sets the data.
     *
     * @param mixed $data
     *
     * @return static
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Gets the data.
     *
     * @return null|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the HTTP status code.
     *
     * @return static
     */
    public function setStatusCode(?int $code): self
    {
        if (null !== $code) {
            $this->statusCode = $code;
        }

        return $this;
    }

    /**
     * Gets the HTTP status code.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Sets the format.
     *
     * @return static
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Gets the format.
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * Sets the serialization context.
     *
     * @return static
     */
    public function setContext(Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Gets the serialization context.
     */
    public function getContext(): Context
    {
        if (null === $this->context) {
            $this->context = new Context();
        }

        return $this->context;
    }

    /**
     * Sets the response.
     *
     * @return static
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Gets the response.
     */
    public function getResponse(): Response
    {
        if (null === $this->response) {
            $this->response = new Response();

            if (null !== ($code = $this->getStatusCode())) {
                $this->response->setStatusCode($code);
            }
        }

        return $this->response;
    }

    /**
     * Sets a header.
     *
     * @return static
     */
    public function setHeader(string $name, string $value): self
    {
        $this->getResponse()->headers->set($name, $value);

        return $this;
    }

    /**
     * Sets the headers.
     *
     * @return static
     */
    public function setHeaders(array $headers): self
    {
        $this->getResponse()->headers->replace($headers);

        return $this;
    }

    /**
     * Gets the headers.
     */
    public function getHeaders(): array
    {
        return $this->getResponse()->headers->all();
    }
}
