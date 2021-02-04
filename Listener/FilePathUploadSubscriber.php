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

use Klipper\Bundle\ApiBundle\Uploader\FilePathUploadListenerConfigInterface;
use Klipper\Component\Content\Uploader\Event\UploadFileCompletedEvent;
use Klipper\Component\Content\Util\ContentUtil;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Contracts\Model\FilePathInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilePathUploadSubscriber implements EventSubscriberInterface
{
    private DomainManagerInterface $domainManager;

    private Filesystem $fs;

    private array $configs = [];

    public function __construct(
        DomainManagerInterface $domainManager,
        ?Filesystem $fs = null
    ) {
        $this->domainManager = $domainManager;
        $this->fs = $fs ?? new Filesystem();
    }

    public static function getSubscribedEvents(): iterable
    {
        return [
            UploadFileCompletedEvent::class => [
                ['onUploadRequest', 0],
            ],
        ];
    }

    public function addFilePathUploadListenerConfig(FilePathUploadListenerConfigInterface $config): void
    {
        $this->configs[] = $config;
    }

    /**
     * @throws
     */
    public function onUploadRequest(UploadFileCompletedEvent $event): void
    {
        foreach ($this->configs as $config) {
            if ($this->doOnUploadRequest($event, $config)) {
                break;
            }
        }
    }

    /**
     * @throws
     */
    public function doOnUploadRequest(
        UploadFileCompletedEvent $event,
        FilePathUploadListenerConfigInterface $config
    ): bool {
        $file = $event->getFile()->getPathname();
        $payload = $event->getPayload();

        if (!\is_object($payload) || !$payload instanceof FilePathInterface
                || $config->getUploaderName() !== $event->getConfig()->getName()
                || !$config->validateEvent($event)) {
            return false;
        }

        $previousFile = $payload->getFilePath();
        $payload->setFilePath(ContentUtil::getRelativePath($this->fs, $event->getConfig(), $file));

        if ($this->domainManager->has(ClassUtils::getClass($payload))) {
            $res = $this->domainManager->get(ClassUtils::getClass($payload))->upsert($payload);

            if (!$res->isValid()) {
                $this->fs->remove($file);

                throw new ConstraintViolationException($res->getErrors());
            }
        }

        if (null !== $previousFile) {
            try {
                $this->fs->remove(ContentUtil::getAbsolutePath($event->getConfig(), $previousFile));
            } catch (\Throwable $e) {
                // no check to optimize request to delete file, so do nothing on error
            }
        }

        return true;
    }
}
