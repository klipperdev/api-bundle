<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller;

use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;
use Klipper\Bundle\ApiBundle\View\Transformer\ViewTransformerInterface;

/**
 * Registry of view transformer for the standard controller.
 *
 * It exists to allow the using of view transformers with dependencies
 * and that are defined by a string in the metadata action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ControllerViewTransformerRegistry
{
    /**
     * @var ViewTransformerInterface[]
     */
    private array $viewTransformers;

    /**
     * @param ViewTransformerInterface[] $viewTransformers
     */
    public function __construct(array $viewTransformers)
    {
        foreach ($viewTransformers as $viewTransformer) {
            $this->add($viewTransformer);
        }
    }

    public function add(ViewTransformerInterface $viewTransformer): self
    {
        $this->viewTransformers[\get_class($viewTransformer)] = $viewTransformer;

        return $this;
    }

    public function has(string $viewTransformerClass): bool
    {
        return isset($this->viewTransformers[$viewTransformerClass]);
    }

    public function get(string $viewTransformerClass): ViewTransformerInterface
    {
        if (!$this->has($viewTransformerClass)) {
            throw new InvalidArgumentException(sprintf(
                'The view transformer "%s" is not registered',
                $viewTransformerClass
            ));
        }

        return $this->viewTransformers[$viewTransformerClass];
    }
}
