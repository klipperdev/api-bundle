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

/**
 * Trait for action with process form.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait ProcessFormTrait
{
    private bool $processForm = true;

    public function isProcessForm(): bool
    {
        return $this->processForm;
    }

    public function setProcessForm(bool $processForm): self
    {
        $this->processForm = $processForm;

        return $this;
    }
}
