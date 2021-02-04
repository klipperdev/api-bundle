<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Uploader;

use Klipper\Component\Content\Uploader\Event\UploadFileCompletedEvent;
use Klipper\Component\Object\Util\ClassUtil;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilePathUploadListenerConfig implements FilePathUploadListenerConfigInterface
{
    private string $uploaderName;
    private string $objectClass;

    public function __construct(string $uploaderName, string $objectClass)
    {
        $this->uploaderName = $uploaderName;
        $this->objectClass = $objectClass;
    }

    public function getUploaderName(): string
    {
        return $this->uploaderName;
    }

    public function validateEvent(UploadFileCompletedEvent $event): bool
    {
        return ClassUtil::isInstanceOf($event->getPayload(), $this->objectClass);
    }
}
