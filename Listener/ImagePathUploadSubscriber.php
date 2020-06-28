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

use Klipper\Bundle\ApiBundle\Uploader\ImagePathUploadListenerConfigInterface;
use Klipper\Component\Content\ImageManipulator\Cache\CacheInterface;
use Klipper\Component\Content\Uploader\Event\UploadFileCompletedEvent;
use Klipper\Component\Content\Util\ContentUtil;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Contracts\Model\ImagePathInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ImagePathUploadSubscriber implements EventSubscriberInterface
{
    private DomainManagerInterface $domainManager;

    private ?CacheInterface $imageManipulatorCache;

    private Filesystem $fs;

    private array $configs = [];

    public function __construct(
        DomainManagerInterface $domainManager,
        ?CacheInterface $imageManipulatorCache = null,
        ?Filesystem $fs = null
    ) {
        $this->domainManager = $domainManager;
        $this->imageManipulatorCache = $imageManipulatorCache;
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

    public function addImagePathUploadListenerConfig(ImagePathUploadListenerConfigInterface $config): void
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
        ImagePathUploadListenerConfigInterface $config
    ): bool {
        $file = $event->getFile()->getPathname();
        $payload = $event->getPayload();

        if (!\is_object($payload) || !$payload instanceof ImagePathInterface
                || $config->getUploaderName() !== $event->getConfig()->getName()
                || !$config->validateEvent($event)) {
            return false;
        }

        $previousFile = $payload->getImagePath();
        $payload->setImagePath(ContentUtil::getRelativePath($this->fs, $event->getConfig(), $file));
        $res = $this->domainManager->get(ClassUtils::getClass($payload))->update($payload);

        if (!$res->isValid()) {
            $this->fs->remove($file);

            throw new ConstraintViolationException($res->getErrors());
        }

        try {
            $this->fs->remove(ContentUtil::getAbsolutePath($event->getConfig(), $previousFile));
        } catch (\Throwable $e) {
            // no check to optimize request to delete file, so do nothing on error
        }

        try {
            if (null !== $this->imageManipulatorCache) {
                $this->imageManipulatorCache->clear(
                    ContentUtil::getAbsolutePath($event->getConfig(), $previousFile)
                );
            }
        } catch (\Throwable $e) {
            // no check to optimize request to delete file, so do nothing on error
        }

        return true;
    }
}
