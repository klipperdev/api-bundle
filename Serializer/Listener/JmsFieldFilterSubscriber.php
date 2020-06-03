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
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JmsFieldFilterSubscriber implements EventSubscriberInterface
{
    protected MetadataManagerInterface $metadataManager;

    protected RequestStack $requestStack;

    protected ?array $cacheFields = null;

    /**
     * @param MetadataManagerInterface $metadataManager The metadata manager
     * @param RequestStack             $requestStack    The request stack
     */
    public function __construct(MetadataManagerInterface $metadataManager, RequestStack $requestStack)
    {
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

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
    private function getMetadataFieldName(ObjectMetadataInterface $meta, $propertyName)
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

    /**
     * Get the request query search.
     */
    private function getFields(): array
    {
        if (null === $this->cacheFields) {
            $this->cacheFields = [];
            $requestFields = $this->getRequestFields();

            if (!empty($requestFields)) {
                $fields = array_map('trim', explode(',', $requestFields));

                foreach ($fields as $field) {
                    $exp = array_map('trim', explode('#', $field));

                    if (2 === \count($exp)) {
                        $this->cacheFields[$exp[0]][$exp[1]] = true;
                    } else {
                        $this->cacheFields['_global'][$exp[0]] = true;
                    }
                }
            }
        }

        return $this->cacheFields;
    }

    /**
     * Get the fields config in request.
     */
    private function getRequestFields(): string
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            if ($request->headers->has('x-fields')) {
                return (string) $request->headers->get('x-fields', '');
            }

            return (string) $request->query->get('fields', '');
        }

        return '';
    }
}
