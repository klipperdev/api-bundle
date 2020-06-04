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

use Klipper\Bundle\ApiBundle\Action\Create;
use Klipper\Bundle\ApiBundle\Action\Creates;
use Klipper\Bundle\ApiBundle\Action\Delete;
use Klipper\Bundle\ApiBundle\Action\Deletes;
use Klipper\Bundle\ApiBundle\Action\Undelete;
use Klipper\Bundle\ApiBundle\Action\Undeletes;
use Klipper\Bundle\ApiBundle\Action\Update;
use Klipper\Bundle\ApiBundle\Action\Updates;
use Klipper\Bundle\ApiBundle\Action\Upsert;
use Klipper\Bundle\ApiBundle\Action\Upserts;
use Klipper\Bundle\ApiBundle\Controller\Action\ActionInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\ActionListInterface;
use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;
use Klipper\Bundle\ApiBundle\View\Transformer\ViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\View;
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\TranslatableRepositoryInterface;
use Klipper\Component\Metadata\ActionMetadataInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Exception\InvalidResourceException;
use Klipper\Component\Resource\Handler\DomainFormConfigList;
use Klipper\Component\Resource\Handler\FormConfigInterface;
use Klipper\Component\Resource\Handler\FormConfigListInterface;
use Klipper\Component\Security\Permission\PermVote;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Standard controller for API.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class StandardController extends AbstractController
{
    /**
     * Standard action to paginate the entities.
     *
     * @param Request $request The request
     */
    public function listAction(Request $request): Response
    {
        $class = $request->attributes->get('_action_class');
        $repo = $this->getDomain($class)->getRepository();
        $method = $request->attributes->get('_method_repository', 'createQueryBuilder');
        $alias = $request->attributes->get('_method_repository_alias', 'o');
        $indexBy = $request->attributes->get('_method_repository_index_by');
        $this->defineView($request);
        $this->checkSecurity($request, 'view');

        return $this->views($repo->{$method}($alias, $indexBy));
    }

    /**
     * Standard action to create an entity.
     *
     * @param Request $request The request
     */
    public function createAction(Request $request): Response
    {
        $meta = $this->getMetadataManager()->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Create::build($this->getFormType($meta), $meta->getClass());
        $this->defineControllerAction($request, $meta, $actionMeta, $action);
        $this->checkSecurity($request, 'create');

        return $this->create($action);
    }

    /**
     * Standard action to create entities.
     *
     * @param Request $request The request
     */
    public function createsAction(Request $request): Response
    {
        $meta = $this->getMetadataManager()->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Creates::build($this->getFormType($meta), $meta->getClass());
        $this->defineControllerActionList($request, $meta, $actionMeta, $action);
        $this->checkSecurity($request, 'create');

        return $this->creates($action);
    }

    /**
     * Standard action to create or update an entity.
     * The id must be added in the request body to update the entity.
     *
     * @param Request $request The request
     */
    public function upsertAction(Request $request): Response
    {
        $meta = $this->getMetadataManager()->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $value = $this->getObjectOrClass($request, $meta);
        $action = Upsert::build($this->getFormType($meta), $value);
        $this->defineControllerAction($request, $meta, $actionMeta, $action);
        $this->checkSecurity($request, \is_object($value) ? 'update' : 'create');

        return $this->upsert($action);
    }

    /**
     * Standard action to create or update entities.
     * The id must be added in each entity of the request body to update the entities.
     *
     * @param Request $request The request
     */
    public function upsertsAction(Request $request): Response
    {
        $meta = $this->getMetadataManager()->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Upserts::build($this->getFormType($meta), $meta->getClass());
        $this->defineControllerActionList($request, $meta, $actionMeta, $action);
        $this->checkSecurity($request, ['create', 'update']);

        return $this->upserts($action);
    }

    /**
     * Standard action to view an entity.
     *
     * @param Request    $request The request
     * @param int|string $id      The identity
     */
    public function viewAction(Request $request, $id): Response
    {
        $class = $request->attributes->get('_action_class');
        $object = $this->getObject($request, $this->getDomain($class), $id);
        $this->defineView($request);
        $this->checkSecurity($request, 'view', $id);

        return $this->view($object);
    }

    /**
     * Standard action to update an entity.
     *
     * @param Request    $request The request
     * @param int|string $id      The identity
     */
    public function updateAction(Request $request, $id): Response
    {
        $class = $request->attributes->get('_action_class');
        $meta = $this->getMetadataManager()->get($class);
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $object = $this->getObject($request, $this->getDomain($class), $id);

        $action = Update::build($this->getFormType($meta), $object);
        $this->defineControllerAction($request, $meta, $actionMeta, $action);
        $this->checkSecurity($request, 'update', $id);

        return $this->update($action);
    }

    /**
     * Standard action to update entities.
     *
     * @param Request $request The request
     */
    public function updatesAction(Request $request): Response
    {
        $meta = $this->getMetadataManager()->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Updates::build($this->getFormType($meta), $meta->getClass());
        $this->defineControllerActionList($request, $meta, $actionMeta, $action);
        $this->checkSecurity($request, 'update');

        return $this->updates($action);
    }

    /**
     * Standard action to delete an entity.
     *
     * @param Request    $request The request
     * @param int|string $id      The identity
     */
    public function deleteAction(Request $request, $id): Response
    {
        $class = $request->attributes->get('_action_class');
        $object = $this->getObject($request, $this->getDomain($class), $id);
        $this->defineView($request);
        $this->checkSecurity($request, 'delete', $id);

        $action = Delete::build($object);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));

        return $this->delete($action);
    }

    /**
     * Standard action to delete entities.
     *
     * @param Request $request The request
     */
    public function deletesAction(Request $request): Response
    {
        $config = $this->getFormConfigList($request);
        $dataList = $this->getDataList($request, $config);
        $action = Deletes::build($dataList);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));
        $this->checkSecurity($request, 'delete');

        return $this->deletes($action);
    }

    /**
     * Standard action to undelete an entity.
     *
     * @param Request    $request The request
     * @param int|string $id      The identity
     */
    public function undeleteAction(Request $request, $id): Response
    {
        $class = $request->attributes->get('_action_class');
        $object = $this->getObject($request, $this->getDomain($class), $id);
        $this->defineView($request);
        $this->checkSecurity($request, 'undelete', $id);

        $action = Undelete::build($object);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));

        return $this->undelete($action);
    }

    /**
     * Standard action to undelete entities.
     *
     * @param Request $request The request
     */
    public function undeletesAction(Request $request): Response
    {
        $config = $this->getFormConfigList($request);
        $dataList = $this->getDataList($request, $config);
        $action = Undeletes::build($dataList);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));
        $this->checkSecurity($request, 'undelete');

        return $this->undeletes($action);
    }

    /**
     * Define the view groups.
     *
     * @param Request $request The request
     */
    protected function defineView(Request $request): void
    {
        $statusCode = $request->attributes->getInt('_view_status_code');
        $groups = (array) $request->attributes->get('_view_groups', []);
        $view = null;

        if (!empty($groups)) {
            $view = View::create();
            $view->getContext()->addGroups($groups);
        }

        if ($statusCode > 0) {
            $view = $view ?? View::create();
            $view->setStatusCode($statusCode);
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $this->defineViewTransformer($request);
    }

    /**
     * Find the view transformer and add the view transformer in the view.
     *
     * @param Request $request The request
     */
    protected function defineViewTransformer(Request $request): void
    {
        $viewTransformer = $request->attributes->get('_view_transformer');

        if (null !== $viewTransformer) {
            if ($this->has($viewTransformer)) {
                $viewTransformer = $this->get($viewTransformer);

                if ($viewTransformer instanceof ViewTransformerInterface) {
                    $this->addViewTransformer($viewTransformer);
                }
            } elseif (class_exists($viewTransformer)) {
                $this->addViewTransformer($viewTransformer());
            }
        }
    }

    /**
     * Define the parameters of the form action.
     *
     * @param Request                             $request    The request
     * @param ObjectMetadataInterface             $meta       The object metadata
     * @param ActionMetadataInterface             $actionMeta The action metadata
     * @param ActionInterface|FormConfigInterface $action     The controller action
     */
    protected function defineControllerAction(
        Request $request,
        ObjectMetadataInterface $meta,
        ActionMetadataInterface $actionMeta,
        FormConfigInterface $action
    ): void {
        $this->defineView($request);

        $action->setOptions($meta->getFormOptions());
        $action->setMethod($this->getMethod($actionMeta));
        $action->setConverter($request->attributes->get('_format', 'json'));
    }

    /**
     * Define the parameters of the controller action list.
     *
     * @param Request                 $request    The request
     * @param ObjectMetadataInterface $meta       The object metadata
     * @param ActionMetadataInterface $actionMeta The action metadata
     * @param ActionListInterface     $action     The controller action list
     */
    protected function defineControllerActionList(
        Request $request,
        ObjectMetadataInterface $meta,
        ActionMetadataInterface $actionMeta,
        ActionListInterface $action
    ): void {
        $this->defineControllerAction($request, $meta, $actionMeta, $action);

        $action->setTransactional($this->getControllerHandler()->isTransactional());
        $action->setLimit($this->getLimit($request));
    }

    /**
     * Get the request record limit.
     *
     * @param Request $request The request
     */
    protected function getLimit(Request $request): ?int
    {
        $max = $this->getFormHandler()->getMaxLimit();
        $limit = $request->attributes->getInt('_request_limit', $this->getFormHandler()->getDefaultLimit());
        $limit = null !== $limit && null !== $max ? min($max, $limit) : $limit;

        return null !== $limit ? max(1, $limit) : $limit;
    }

    /**
     * Get the form type of object metadata.
     *
     * @param ObjectMetadataInterface $meta The object metadata
     */
    protected function getFormType(ObjectMetadataInterface $meta): string
    {
        $formType = $meta->getFormType();

        if (null === $formType) {
            throw new InvalidArgumentException(sprintf(
                'The metadata form type of the "%s" class is required to use the standard controller',
                $meta->getClass()
            ));
        }

        return $formType;
    }

    /**
     * Get the route method of the action metadata.
     *
     * @param ActionMetadataInterface $meta The action metadata
     */
    protected function getMethod(ActionMetadataInterface $meta): string
    {
        $method = current($meta->getMethods());

        if (false === $method) {
            throw new InvalidArgumentException(sprintf(
                'The route method of the action metadata "%s" is required for the object metadata "%s"',
                $meta->getName(),
                $meta->getParent()->getClass()
            ));
        }

        return $method;
    }

    /**
     * Get the form config list.
     *
     * @param Request $request The request
     */
    protected function getFormConfigList(Request $request): DomainFormConfigList
    {
        $meta = $this->getMetadataManager()->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));

        $config = new DomainFormConfigList(
            $this->getDomain($meta->getClass()),
            '',
            [],
            $this->getMethod($actionMeta),
            $request->attributes->get('_format', 'json')
        );
        $config->setLimit($this->getLimit($request));

        return $config;
    }

    /**
     * Get the data list of the form config.
     *
     * @param Request                 $request The request
     * @param FormConfigListInterface $config  The form config
     */
    protected function getDataList(Request $request, FormConfigListInterface $config): array
    {
        $converter = $this->getConverterRegistry()->get($config->getConverter());
        $dataList = $converter->convert((string) $request->getContent());
        $limit = $config->getLimit();

        try {
            $dataList = $config->findList($dataList);
        } catch (InvalidResourceException $e) {
            throw new InvalidResourceException($this->getTranslator()->trans('form_handler.results_field_required', [], 'KlipperResource'));
        }

        if (null !== $limit && \count($dataList) > $limit) {
            $msg = $this->getTranslator()->trans('form_handler.size_exceeds', [
                '{{ limit }}' => $limit,
            ], 'KlipperResource');

            throw new InvalidResourceException(sprintf($msg, $limit));
        }

        return $dataList;
    }

    /**
     * Get the object instance or the classname.
     *
     * @param Request                 $request The request
     * @param ObjectMetadataInterface $meta    The object metadata
     *
     * @return object|string
     */
    protected function getObjectOrClass(Request $request, ObjectMetadataInterface $meta)
    {
        $class = $meta->getClass();
        $domain = $this->getDomain($class);
        $converter = $this->getConverterRegistry()->get($request->attributes->get('_format', 'json'));
        $data = $converter->convert((string) $request->getContent());
        $classMeta = $domain->getObjectManager()->getClassMetadata($class);
        $fieldId = current($classMeta->getIdentifierFieldNames());
        $object = null;

        if (\is_array($data) && isset($data[$fieldId])) {
            $object = $this->getObject($request, $domain, $data[$fieldId]);
        }

        return $object ?? $class;
    }

    /**
     * Get the object instance of id.
     *
     * @param Request         $request The request
     * @param DomainInterface $domain  The domain
     * @param int|string      $id      The id
     *
     * @throws NotFoundHttpException
     */
    protected function getObject(Request $request, DomainInterface $domain, $id): object
    {
        $repo = $domain->getRepository();

        if (null !== $expr = $request->attributes->get('_repository_expr')) {
            $object = $this->getRepositoryExpressionLanguage()->evaluate($expr, [
                'repository' => $repo,
                'id' => $id,
            ]);
        } else {
            $defaultMet = $repo instanceof TranslatableRepositoryInterface ? 'findOneTranslatedById' : 'findOneById';
            $method = $request->attributes->get('_repository_method', $defaultMet);
            $object = $repo->{$method}($id);
        }

        if (null === $object) {
            throw $this->createNotFoundException();
        }

        return $object;
    }

    /**
     * @param Request         $request The request
     * @param string|string[] $actions The action names
     * @param null|int|string $id      The id
     */
    protected function checkSecurity(Request $request, $actions, $id = null): void
    {
        $actions = (array) $actions;
        $class = $request->attributes->get('_action_class');
        $subject = null !== $id ? [$class, $id] : $class;

        foreach ($actions as $action) {
            if (class_exists($class) && !$this->getAuthorizationChecker()->isGranted(new PermVote($action), $subject)) {
                throw $this->createAccessDeniedException();
            }
        }
    }
}
