<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Serializer\Listener;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JmsExtraValueSubscriber extends AbstractJmsFilterSubscriber
{
    private PropertyNamingStrategyInterface $propertyNamingStrategy;

    private array $cache = [];

    public function __construct(
        MetadataManagerInterface $metadataManager,
        RequestStack $requestStack,
        PropertyNamingStrategyInterface $propertyNamingStrategy
    ) {
        parent::__construct($metadataManager, $requestStack);

        $this->propertyNamingStrategy = $propertyNamingStrategy;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $visitor = $event->getVisitor();
        $object = $event->getObject();

        if ((!\is_object($object) && !\is_array($object))
            || (\is_array($object) && !isset($event->getType()['name']))
            || !$visitor instanceof SerializationVisitorInterface
        ) {
            return;
        }

        /** @var array|object $object */
        $class = \is_object($object) ? ClassUtils::getClass($object) : ClassUtils::getRealClass($event->getType()['name']);

        if (!\array_key_exists($class, $this->cache)) {
            $classMeta = $event->getContext()->getMetadataFactory()->getMetadataForClass($class);

            if (null === $classMeta || !$this->metadataManager->has($classMeta->name)) {
                $this->cache[$class] = false;
            } else {
                $this->cache[$class] = $this->metadataManager->get($classMeta->name)->getName();
            }
        }

        $metaName = $this->cache[$class];

        if (false === $metaName) {
            return;
        }

        /** @var string[] $propNames */
        $propNames = \is_object($object) ? array_keys(get_object_vars($object)) : array_keys($object);

        foreach ($propNames as $propName) {
            if (0 === strpos($propName, '@')) {
                $staticPropMeta = new StaticPropertyMetadata($class, $propName, null);
                $staticPropMeta->serializedName = null;
                $staticPropMeta->serializedName = $this->propertyNamingStrategy->translateName($staticPropMeta);

                if (empty($fields)
                    || isset($fields['_global'][$staticPropMeta->serializedName])
                    || isset($fields[$metaName][$staticPropMeta->serializedName])
                ) {
                    $visitor->visitProperty(
                        $staticPropMeta,
                        \is_object($object) ? $object->{$propName} : $object[$propName]
                    );
                }
            }
        }
    }
}
