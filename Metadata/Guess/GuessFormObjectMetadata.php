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

use Klipper\Bundle\ApiBundle\Form\Type\ObjectMetadataType;
use Klipper\Component\Metadata\Guess\GuessObjectConfigInterface;
use Klipper\Component\Metadata\ObjectMetadataBuilderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GuessFormObjectMetadata implements GuessObjectConfigInterface
{
    public function guessObjectConfig(ObjectMetadataBuilderInterface $builder): void
    {
        if (null === $builder->getFormType()) {
            $builder->setFormType(ObjectMetadataType::class);
            $builder->setFormOptions(array_merge($builder->getFormOptions(), [
                'data_class' => $builder->getClass(),
            ]));
        }
    }
}
