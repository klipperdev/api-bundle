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
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Metadata\ObjectMetadataInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JmsFieldFilterSubscriber extends AbstractJmsFilterSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
            ],
        ];
    }

    /**
     * Replace url generator aliases by her real classname and inject object in property meta.
     *
     * @param ObjectEvent $event The event
     */
    public function onPreSerialize(ObjectEvent $event): void
    {
        if (!\is_object($event->getObject()) || empty($fields = $this->getFields())) {
            return;
        }

        /** @var object $object */
        $object = $event->getObject();
        $classMeta = $event->getContext()->getMetadataFactory()->getMetadataForClass(ClassUtils::getClass($object));

        if (null === $classMeta || !$this->metadataManager->has($classMeta->name)) {
            return;
        }

        $meta = $this->metadataManager->get($classMeta->name);
        $metaName = $meta->getName();

        foreach (array_keys($classMeta->propertyMetadata) as $propertyName) {
            $fieldMetaName = $this->getMetadataFieldName($meta, $propertyName);

            if (!isset($fields['_global'][$fieldMetaName])
                    && !isset($fields[$metaName][$fieldMetaName])) {
                unset($classMeta->propertyMetadata[$propertyName]);
            }
        }
    }

    /**
     * Get the field metadata name by the class property name.
     *
     * @param ObjectMetadataInterface $meta         The object metadata
     * @param string                  $propertyName The property name of class
     *
     * @return false|string
     */
    private function getMetadataFieldName(ObjectMetadataInterface $meta, string $propertyName)
    {
        $fieldMetaName = false;

        if ($meta->hasField($propertyName)) {
            $fieldMetaName = $meta->getField($propertyName)->getName();
        } else {
            if (false !== ($pos = strrpos($propertyName, 'Id'))) {
                $assoPropName = substr($propertyName, 0, $pos);
            } else {
                $assoPropName = $propertyName;
            }

            if ($meta->hasAssociation($assoPropName)) {
                $fieldMetaName = $meta->getAssociation($assoPropName)->getName();
            }
        }

        return $fieldMetaName;
    }
}
