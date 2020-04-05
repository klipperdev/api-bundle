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
    private $massActions;

    public function __construct(bool $massActions = true)
    {
        $this->massActions = $massActions;
    }

    /**
     * {@inheritdoc}
     */
    public function guessObjectConfig(ObjectMetadataBuilderInterface $metadata): void
    {
        if (false === $metadata->getBuildDefaultActions()) {
            return;
        }

        $undeletable = interface_exists(SoftDeletableInterface::class)
            && is_a($metadata->getClass(), SoftDeletableInterface::class, true);

        $metadata
            ->addAction(new ActionMetadataBuilder('list'))
            ->addAction(new ActionMetadataBuilder('create'))
            ->addAction(new ActionMetadataBuilder('upsert'))
            ->addAction(new ActionMetadataBuilder('view'))
            ->addAction(new ActionMetadataBuilder('update'))
            ->addAction(new ActionMetadataBuilder('delete'))
        ;

        if ($undeletable) {
            $metadata->addAction(new ActionMetadataBuilder('undelete'));
        }

        if ($this->massActions) {
            $metadata
                ->addAction(new ActionMetadataBuilder('creates'))
                ->addAction(new ActionMetadataBuilder('upserts'))
                ->addAction(new ActionMetadataBuilder('updates'))
                ->addAction(new ActionMetadataBuilder('deletes'))
            ;

            if ($undeletable) {
                $metadata->addAction(new ActionMetadataBuilder('undeletes'));
            }
        }
    }
}
