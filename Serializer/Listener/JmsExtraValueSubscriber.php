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
        if ((!\is_object($event->getObject()) && !\is_array($event->getObject()))
            || !$event->getVisitor() instanceof SerializationVisitorInterface
        ) {
            return;
        }

        /** @var SerializationVisitorInterface $visitor */
        $visitor = $event->getVisitor();

        /** @var array|object $object */
        $object = $event->getObject();
        $classMeta = $event->getContext()->getMetadataFactory()->getMetadataForClass(ClassUtils::getClass($object));
        $fields = $this->getFields();

        if (null === $classMeta || !$this->metadataManager->has($classMeta->name)) {
            return;
        }

        $meta = $this->metadataManager->get($classMeta->name);
        $metaName = $meta->getName();

        /** @var string[] $propNames */
        $propNames = \is_object($object) ? array_keys(get_object_vars($object)) : array_keys($object);

        foreach ($propNames as $propName) {
            if (0 === strpos($propName, '@')) {
                $staticPropMeta = new StaticPropertyMetadata($classMeta->name, $propName, null);
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
