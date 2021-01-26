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

use Klipper\Bundle\ApiBundle\Controller\Action\ActionInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\ListenerFormBuilderHandler;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ActionWithClassTrait;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\NewOptionsInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\NewOptionsTrait;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ProcessFormInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\ProcessFormTrait;
use Klipper\Component\Resource\Handler\FormConfig;

/**
 * Create action.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Create extends FormConfig implements ActionInterface, ProcessFormInterface, NewOptionsInterface
{
    use ActionWithClassTrait;
    use NewOptionsTrait;
    use ProcessFormTrait;

    private function __construct(string $formType, string $class)
    {
        parent::__construct($formType);

        $this->setClass($class);
        $this->addBuilderHandler(new ListenerFormBuilderHandler($this));
    }
}
