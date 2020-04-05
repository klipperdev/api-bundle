<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Controller\Action;

use Klipper\Component\Resource\Handler\FormConfigInterface;

/**
 * Interface of list action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ActionListInterface extends FormConfigInterface, CommonActionInterface
{
    /**
     * Set the limit of the size list.
     *
     * @param null|int $limit The limit
     *
     * @return static
     */
    public function setLimit(?int $limit);

    /**
     * Get the limit of the size list.
     */
    public function getLimit(): ?int;

    /**
     * Set the transactional mode.
     *
     * @param bool $transactional Check if the domain use the transactional mode
     *
     * @return static
     */
    public function setTransactional(?bool $transactional);

    /**
     * Check if the domain use the transactional mode.
     *
     * @return bool
     */
    public function isTransactional(): ?bool;
}
