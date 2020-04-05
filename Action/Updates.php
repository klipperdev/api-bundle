<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Action;

use Klipper\Bundle\ApiBundle\Controller\Action\ActionListInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\ListenerFormBuilderHandler;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ListTrait;
use Klipper\Component\Resource\Handler\FormConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Updates action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Updates extends FormConfig implements ActionListInterface
{
    use ListTrait;

    private function __construct(string $formType, string $class)
    {
        parent::__construct($formType);

        $this->setClass($class);
        $this->setMethod(Request::METHOD_PATCH);
        $this->addBuilderHandler(new ListenerFormBuilderHandler($this));
    }
}
