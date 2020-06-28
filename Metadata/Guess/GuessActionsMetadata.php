<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Metadata\Guess;

use Klipper\Component\Metadata\ActionMetadataBuilder;
use Klipper\Component\Metadata\Guess\GuessObjectConfigInterface;
use Klipper\Component\Metadata\ObjectMetadataBuilderInterface;
use Klipper\Component\Resource\Model\SoftDeletableInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GuessActionsMetadata implements GuessObjectConfigInterface
{
    private bool $massActions;

    public function __construct(bool $massActions = true)
    {
        $this->massActions = $massActions;
    }

    public function guessObjectConfig(ObjectMetadataBuilderInterface $metadata): void
    {
        if (false === $metadata->getBuildDefaultActions()) {
            return;
        }

        $undeletable = interface_exists(SoftDeletableInterface::class)
            && is_a($metadata->getClass(), SoftDeletableInterface::class, true);

        $this->addAction($metadata, 'list');
        $this->addAction($metadata, 'create');
        $this->addAction($metadata, 'upsert');
        $this->addAction($metadata, 'view');
        $this->addAction($metadata, 'update');
        $this->addAction($metadata, 'delete');

        if ($undeletable) {
            $this->addAction($metadata, 'undelete');
        }

        if ($this->massActions) {
            $this->addAction($metadata, 'creates');
            $this->addAction($metadata, 'upserts');
            $this->addAction($metadata, 'updates');
            $this->addAction($metadata, 'deletes');

            if ($undeletable) {
                $this->addAction($metadata, 'undeletes');
            }
        }
    }

    private function addAction(ObjectMetadataBuilderInterface $metadata, string $name): void
    {
        if (!\in_array($name, $metadata->getExcludedDefaultActions(), true)) {
            $metadata->addAction(new ActionMetadataBuilder($name));
        }
    }
}
