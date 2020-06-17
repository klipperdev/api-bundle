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

use Klipper\Component\Metadata\ActionMetadataBuilderInterface;
use Klipper\Component\Metadata\Guess\GuessActionConfigInterface;
use Klipper\Component\Metadata\ObjectMetadataBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GuessActionMetadata implements GuessActionConfigInterface
{
    public const ACTIONS = [
        'list' => 'guessList',
        'create' => 'guessCreate',
        'creates' => 'guessCreates',
        'upsert' => 'guessUpsert',
        'upserts' => 'guessUpserts',
        'view' => 'guessView',
        'update' => 'guessUpdate',
        'updates' => 'guessUpdates',
        'delete' => 'guessDelete',
        'deletes' => 'guessDeletes',
        'undelete' => 'guessUndelete',
        'undeletes' => 'guessUndeletes',
    ];

    public function guessActionConfig(ActionMetadataBuilderInterface $builder): void
    {
        $actionName = $builder->getName();

        if (isset(static::ACTIONS[$actionName])) {
            $this->{static::ACTIONS[$actionName]}($builder, $builder->getParent());
        }
    }

    /**
     * Guess the list action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessList(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'list',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_GET]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()));
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::listAction');
        }
    }

    /**
     * Guess the create action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessCreate(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'create',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_POST]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()));
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::createAction');
        }
    }

    /**
     * Guess the creates action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessCreates(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'creates',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_POST]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent(), 'batch'));
        }

        $builder->addDefaults([
            '_priority' => -100,
        ]);

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::createsAction');
        }
    }

    /**
     * Guess the upsert action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessUpsert(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'upsert',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_PUT]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()));
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::upsertAction');
        }
    }

    /**
     * Guess the upserts action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessUpserts(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'upserts',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_PUT]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent(), 'batch'));
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::upsertsAction');
        }

        $builder->addDefaults([
            '_priority' => -100,
        ]);
    }

    /**
     * Guess the view action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessView(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'view',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_GET]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()).'/{id}');
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::viewAction');
        }
    }

    /**
     * Guess the update action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessUpdate(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'update',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_PATCH]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()).'/{id}');
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::updateAction');
        }
    }

    /**
     * Guess the updates action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessUpdates(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'updates',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_PATCH]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent(), 'batch'));
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::updatesAction');
        }

        $builder->addDefaults([
            '_priority' => -100,
        ]);
    }

    /**
     * Guess the delete action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessDelete(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'delete',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_DELETE]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()).'/{id}');
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::deleteAction');
        }
    }

    /**
     * Guess the deletes action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessDeletes(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'deletes',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_DELETE]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent(), 'batch'));
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::deletesAction');
        }

        $builder->addDefaults([
            '_priority' => -100,
        ]);
    }

    /**
     * Guess the undelete action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessUndelete(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'undelete',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_PUT]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent()).'/undelete/{id}');
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::undeleteAction');
        }
    }

    /**
     * Guess the undeletes action.
     *
     * @param ActionMetadataBuilderInterface $builder The action metadata builder
     */
    public function guessUndeletes(ActionMetadataBuilderInterface $builder): void
    {
        $builder->addDefaults([
            '_action' => 'undeletes',
            '_action_class' => $builder->getParent()->getClass(),
        ]);

        if (empty($builder->getMethods())) {
            $builder->setMethods([Request::METHOD_PUT]);
        }

        if (null === $builder->getPath()) {
            $builder->setPath($this->getBasePath($builder->getParent(), 'batch').'/undelete');
        }

        if (null === $builder->getController()) {
            $builder->setController('Klipper\Bundle\ApiBundle\Controller\StandardController::undeletesAction');
        }

        $builder->addDefaults([
            '_priority' => -100,
        ]);
    }

    /**
     * Get the base path of route.
     *
     * @param ObjectMetadataBuilderInterface $builder The object metadata builder
     * @param string                         $prefix  The path prefix
     */
    private function getBasePath(ObjectMetadataBuilderInterface $builder, string $prefix = ''): string
    {
        return '/{organization}/'.('' !== $prefix ? $prefix.'/' : '').$builder->getPluralName();
    }
}
