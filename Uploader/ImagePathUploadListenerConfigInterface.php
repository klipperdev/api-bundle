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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ImagePathUploadListenerConfigInterface
{
    public function getUploaderName(): string;

    public function validateEvent(UploadFileCompletedEvent $event): bool;
}
