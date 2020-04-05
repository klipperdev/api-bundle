<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\View;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ConfigurableViewHandlerInterface extends ViewHandlerInterface
{
    /**
     * If nulls should be serialized.
     */
    public function setSerializeNullStrategy(bool $isEnabled): void;

    /**
     * Set the default serialization version.
     */
    public function setExclusionStrategyVersion(string $version): void;

    /**
     * Set the default serialization groups.
     */
    public function setExclusionStrategyGroups(array $groups): void;
}
