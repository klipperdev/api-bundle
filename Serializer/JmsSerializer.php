<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Serializer;

use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as JmsSerializerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JmsSerializer implements SerializerInterface
{
    /**
     * @var JmsSerializerInterface
     */
    private $serializer;

    /**
     * @var null|SerializationContextFactoryInterface
     */
    private $serializationContextFactory;

    /**
     * Constructor.
     *
     * @param JmsSerializerInterface                    $serializer                  The jms serializer
     * @param null|SerializationContextFactoryInterface $serializationContextFactory The jms context factory
     */
    public function __construct(
        JmsSerializerInterface $serializer,
        ?SerializationContextFactoryInterface $serializationContextFactory = null
    ) {
        $this->serializer = $serializer;
        $this->serializationContextFactory = $serializationContextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, string $format, Context $context): string
    {
        $jmsContext = $this->convertContext($context);

        return $this->serializer->serialize($data, $format, $jmsContext);
    }

    /**
     * Convert the context with jms context.
     *
     * @param Context $context The context
     */
    private function convertContext(Context $context): SerializationContext
    {
        $jmsContext = $this->serializationContextFactory
            ? $this->serializationContextFactory->createSerializationContext()
            : SerializationContext::create();

        foreach ($context->getAttributes() as $key => $value) {
            $jmsContext->setAttribute($key, $value);
        }

        if (null !== $context->getVersion()) {
            $jmsContext->setVersion($context->getVersion());
        }

        if (null !== $context->getGroups()) {
            $jmsContext->setGroups($context->getGroups());
        }

        if (true === $context->hasMaxDepthChecks()) {
            $jmsContext->enableMaxDepthChecks();
        }

        if (null !== $context->getSerializeNull()) {
            $jmsContext->setSerializeNull($context->getSerializeNull());
        }

        return $jmsContext;
    }
}
