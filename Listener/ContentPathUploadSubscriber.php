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

use Klipper\Component\Content\ContentManagerInterface;
use Klipper\Component\Content\Uploader\Event\UploadFileCompletedEvent;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ContentPathUploadSubscriber implements EventSubscriberInterface
{
    private DomainManagerInterface $domainManager;

    private ContentManagerInterface $contentManager;

    private string $class;

    private string $property;

    private PropertyAccessor $accessor;

    public function __construct(
        DomainManagerInterface $domainManager,
        ContentManagerInterface $contentManager,
        string $class,
        string $property,
        PropertyAccessor $accessor = null
    ) {
        $this->domainManager = $domainManager;
        $this->contentManager = $contentManager;
        $this->class = $class;
        $this->property = $property;
        $this->accessor = $accessor ?? PropertyAccess::createPropertyAccessor();
    }

    public static function getSubscribedEvents(): iterable
    {
        return [
            UploadFileCompletedEvent::class => [
                ['onUploadRequest', 0],
            ],
        ];
    }

    /**
     * @throws
     */
    public function onUploadRequest(UploadFileCompletedEvent $event): void
    {
        $file = $event->getFile()->getPathname();
        $payload = $event->getPayload();
        $uploaderName = $this->contentManager->getUploaderName($event->getPayload());

        if (!\is_object($payload)
            || !$payload instanceof $this->class
            || null === $uploaderName
        ) {
            return;
        }

        $previousFile = $this->accessor->getValue($payload, $this->property);
        $newFile = $this->contentManager->buildRelativePath($uploaderName, $file);
        $this->accessor->setValue(
            $payload,
            $this->property,
            $newFile
        );

        if ($this->domainManager->has(ClassUtils::getClass($payload))) {
            $res = $this->domainManager->get(ClassUtils::getClass($payload))->upsert($payload);

            if (!$res->isValid()) {
                $this->contentManager->remove($uploaderName, $newFile);

                throw new ConstraintViolationException($res->getErrors());
            }
        }

        if (null !== $previousFile) {
            try {
                $this->contentManager->remove($uploaderName, $previousFile);
            } catch (\Throwable $e) {
                // no check to optimize request to delete file, so do nothing on error
            }
        }
    }
}
