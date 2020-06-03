<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action\Traits;

use Klipper\Bundle\ApiBundle\Controller\Action\ActionListInterface;

/**
 * Trait for the new options.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait ListTrait
{
    use ActionWithClassTrait;

    protected ?int $limit = null;

    protected ?bool $transactional = null;

    /**
     * @see ActionListInterface::setLimit()
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @see ActionListInterface::getLimit()
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @see ActionListInterface::setTransactional()
     */
    public function setTransactional(?bool $transactional): self
    {
        $this->transactional = $transactional;

        return $this;
    }

    /**
     * @see ActionListInterface::isTransactional()
     */
    public function isTransactional(): ?bool
    {
        return $this->transactional;
    }
}
