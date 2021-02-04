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
use Klipper\Contracts\Model\FilePathInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilePathUploadSubscriber implements EventSubscriberInterface
{
    private DomainManagerInterface $domainManager;

    private ContentManagerInterface $contentManager;

    public function __construct(
        DomainManagerInterface $domainManager,
        ContentManagerInterface $contentManager
    ) {
        $this->domainManager = $domainManager;
        $this->contentManager = $contentManager;
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
            || !$payload instanceof FilePathInterface
            || null === $uploaderName
        ) {
            return;
        }

        $previousFile = $payload->getFilePath();
        $payload->setFilePath($this->contentManager->buildRelativePath($uploaderName, $file));

        if ($this->domainManager->has(ClassUtils::getClass($payload))) {
            $res = $this->domainManager->get(ClassUtils::getClass($payload))->upsert($payload);

            if (!$res->isValid()) {
                $this->contentManager->remove($uploaderName, $payload->getFilePath());

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
