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
use Klipper\Bundle\ApiBundle\Controller\Action\ActionListInterface;
use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;
use Klipper\Bundle\ApiBundle\RequestHeaders;
use Klipper\Bundle\ApiBundle\Util\RequestHeaderUtil;
use Klipper\Bundle\ApiBundle\View\View;
use Klipper\Bundle\ApiBundle\ViewGroups;
use Klipper\Bundle\DoctrineExtensionsExtraBundle\Request\ParamConverter\DoctrineParamConverterExpressionLanguage;
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\TranslatableRepositoryInterface;
use Klipper\Component\HttpFoundation\Util\RequestUtil;
use Klipper\Component\Metadata\ActionMetadataInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Klipper\Component\Resource\Converter\ConverterRegistryInterface;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Exception\InvalidResourceException;
use Klipper\Component\Resource\Handler\DomainFormConfigList;
use Klipper\Component\Resource\Handler\FormConfigInterface;
use Klipper\Component\Resource\Handler\FormConfigListInterface;
use Klipper\Component\Security\Permission\PermVote;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Standard controller for API.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class StandardController
{
    private ControllerViewTransformerRegistry $registry;

    public function __construct(ControllerViewTransformerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Standard action to paginate the entities.
     */
    public function listAction(
        Request $request,
        ControllerHelper $helper
    ): Response {
        $class = $request->attributes->get('_action_class');
        $repo = $helper->getRepository($class);
        $defaultRepoMethod = method_exists($repo, 'createTranslatedQueryBuilder')
            ? 'createTranslatedQueryBuilder'
            : 'createQueryBuilder';
        $method = $request->attributes->get('_repository_method', $defaultRepoMethod);
        $alias = $request->attributes->get('_repository_method_alias', 'o');
        $indexBy = $request->attributes->get('_repository_method_index_by');
        $this->addGroups($request, ['Views']);
        $this->defineView($request, $helper);
        $this->checkSecurity($request, $helper, 'view');

        return $helper->views($repo->{$method}($alias, $indexBy));
    }

    /**
     * Standard action to create an entity.
     */
    public function createAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager
    ): Response {
        $meta = $metadataManager->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Create::build($this->getFormType($meta), $meta->getClass());
        $this->addGroups($request, ['View', 'Create']);
        $this->defineControllerAction($request, $helper, $meta, $actionMeta, $action);
        $this->checkSecurity($request, $helper, 'create');

        return $helper->create($action);
    }

    /**
     * Standard action to create entities.
     */
    public function createsAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager
    ): Response {
        $meta = $metadataManager->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Creates::build($this->getFormType($meta), $meta->getClass());
        $this->addGroups($request, ['Views', 'Creates']);
        $this->defineControllerActionList($request, $helper, $meta, $actionMeta, $action);
        $this->checkSecurity($request, $helper, 'create');

        return $helper->creates($action);
    }

    /**
     * Standard action to create or update an entity.
     * The id must be added in the request body to update the entity.
     */
    public function upsertAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager,
        ConverterRegistryInterface $converterRegistry,
        DoctrineParamConverterExpressionLanguage $expressionLanguage
    ): Response {
        $meta = $metadataManager->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $value = $this->getObjectOrClass($request, $helper, $converterRegistry, $expressionLanguage, $meta);
        $action = Upsert::build($this->getFormType($meta), $value);
        $this->addGroups($request, ['View', 'Create', 'Update']);
        $this->defineControllerAction($request, $helper, $meta, $actionMeta, $action);
        $this->checkSecurity($request, $helper, \is_object($value) ? 'update' : 'create');

        return $helper->upsert($action);
    }

    /**
     * Standard action to create or update entities.
     * The id must be added in each entity of the request body to update the entities.
     */
    public function upsertsAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager
    ): Response {
        $meta = $metadataManager->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Upserts::build($this->getFormType($meta), $meta->getClass());
        $this->addGroups($request, ['Views', 'Creates', 'Updates']);
        $this->defineControllerActionList($request, $helper, $meta, $actionMeta, $action);
        $this->checkSecurity($request, $helper, ['create', 'update']);

        return $helper->upserts($action);
    }

    /**
     * Standard action to view an entity.
     *
     * @param int|string $id The identity
     */
    public function viewAction(
        Request $request,
        ControllerHelper $helper,
        DoctrineParamConverterExpressionLanguage $expressionLanguage,
        $id
    ): Response {
        $class = $request->attributes->get('_action_class');
        $object = $this->getObject($request, $helper, $expressionLanguage, $helper->getDomain($class), $id);
        $this->addGroups($request, ['View']);
        $this->defineView($request, $helper);
        $this->checkSecurity($request, $helper, 'view', $id);

        return $helper->view($object);
    }

    /**
     * Standard action to update an entity.
     *
     * @param int|string $id The identity
     */
    public function updateAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager,
        DoctrineParamConverterExpressionLanguage $expressionLanguage,
        $id
    ): Response {
        $class = $request->attributes->get('_action_class');
        $meta = $metadataManager->get($class);
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $object = $this->getObject($request, $helper, $expressionLanguage, $helper->getDomain($class), $id);

        $action = Update::build($this->getFormType($meta), $object);
        $this->addGroups($request, ['View', 'Update']);
        $this->defineControllerAction($request, $helper, $meta, $actionMeta, $action);
        $this->checkSecurity($request, $helper, 'update', $id);

        return $helper->update($action);
    }

    /**
     * Standard action to update entities.
     */
    public function updatesAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager
    ): Response {
        $meta = $metadataManager->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));
        $action = Updates::build($this->getFormType($meta), $meta->getClass());
        $this->addGroups($request, ['Views', 'Updates']);
        $this->defineControllerActionList($request, $helper, $meta, $actionMeta, $action);
        $this->checkSecurity($request, $helper, 'update');

        return $helper->updates($action);
    }

    /**
     * Standard action to delete an entity.
     *
     * @param int|string $id The identity
     */
    public function deleteAction(
        Request $request,
        ControllerHelper $helper,
        DoctrineParamConverterExpressionLanguage $expressionLanguage,
        $id
    ): Response {
        $class = $request->attributes->get('_action_class');
        $object = $this->getObject($request, $helper, $expressionLanguage, $helper->getDomain($class), $id);
        $this->defineView($request, $helper);
        $this->checkSecurity($request, $helper, 'delete', $id);

        $action = Delete::build($object);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));

        return $helper->delete($action);
    }

    /**
     * Standard action to delete entities.
     */
    public function deletesAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager,
        TranslatorInterface $translator,
        ConverterRegistryInterface $converterRegistry
    ): Response {
        $config = $this->getFormConfigList($request, $helper, $metadataManager);
        $dataList = $this->getDataList($request, $translator, $converterRegistry, $config);
        $action = Deletes::build($dataList);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));
        $this->checkSecurity($request, $helper, 'delete');

        return $helper->deletes($action);
    }

    /**
     * Standard action to undelete an entity.
     *
     * @param int|string $id The identity
     */
    public function undeleteAction(
        Request $request,
        ControllerHelper $helper,
        DoctrineParamConverterExpressionLanguage $expressionLanguage,
        $id
    ): Response {
        $class = $request->attributes->get('_action_class');
        $object = $this->getObject($request, $helper, $expressionLanguage, $helper->getDomain($class), $id);
        $this->addGroups($request, ['View', 'Undelete']);
        $this->defineView($request, $helper);
        $this->checkSecurity($request, $helper, 'undelete', $id);

        $action = Undelete::build($object);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));

        return $helper->undelete($action);
    }

    /**
     * Standard action to undelete entities.
     */
    public function undeletesAction(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager,
        TranslatorInterface $translator,
        ConverterRegistryInterface $converterRegistry
    ): Response {
        $config = $this->getFormConfigList($request, $helper, $metadataManager);
        $dataList = $this->getDataList($request, $translator, $converterRegistry, $config);
        $action = Undeletes::build($dataList);
        $this->addGroups($request, ['Views', 'Undeletes']);
        $action->setReturnedObject($request->attributes->getBoolean('_action_returned_object'));
        $this->checkSecurity($request, $helper, 'undelete');

        return $helper->undeletes($action);
    }

    /**
     * Define the view groups.
     */
    protected function defineView(Request $request, ControllerHelper $helper): void
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
            $helper->setView($view);
        }

        $this->defineViewTransformer($request, $helper);
    }

    /**
     * Find the view transformer and add the view transformer in the view.
     */
    protected function defineViewTransformer(Request $request, ControllerHelper $helper): void
    {
        $viewTransformer = $request->attributes->get('_view_transformer');

        if (null !== $viewTransformer) {
            if ($this->registry->has((string) $viewTransformer)) {
                $helper->addViewTransformer($this->registry->get($viewTransformer));
            } elseif (class_exists($viewTransformer)) {
                $helper->addViewTransformer(new $viewTransformer());
            }
        }
    }

    /**
     * Define the parameters of the form action.
     */
    protected function defineControllerAction(
        Request $request,
        ControllerHelper $helper,
        ObjectMetadataInterface $meta,
        ActionMetadataInterface $actionMeta,
        FormConfigInterface $action
    ): void {
        $this->defineView($request, $helper);

        $action->setOptions($meta->getFormOptions());
        $action->setMethod($this->getMethod($actionMeta));
        $action->setConverter($request->attributes->get('_format', 'json'));
    }

    /**
     * Define the parameters of the controller action list.
     */
    protected function defineControllerActionList(
        Request $request,
        ControllerHelper $helper,
        ObjectMetadataInterface $meta,
        ActionMetadataInterface $actionMeta,
        ActionListInterface $action
    ): void {
        $this->defineControllerAction($request, $helper, $meta, $actionMeta, $action);

        $action->setTransactional($helper->isTransactional());
        $action->setLimit($this->getLimit($request, $helper));
    }

    /**
     * Get the request record limit.
     */
    protected function getLimit(Request $request, ControllerHelper $helper): ?int
    {
        $max = $helper->getFormMaxLimit();

        $limit = $request->attributes->has('_request_limit')
            ? $request->attributes->getInt('_request_limit')
            : $helper->getFormDefaultLimit();
        $limit = null !== $limit && null !== $max ? min($max, $limit) : $limit;

        return null !== $limit ? max(1, $limit) : $limit;
    }

    /**
     * Get the form type of object metadata.
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
     */
    protected function getFormConfigList(
        Request $request,
        ControllerHelper $helper,
        MetadataManagerInterface $metadataManager
    ): DomainFormConfigList {
        $meta = $metadataManager->get($request->attributes->get('_action_class'));
        $actionMeta = $meta->getAction($request->attributes->get('_action'));

        $config = new DomainFormConfigList(
            $helper->getDomain($meta->getClass()),
            '',
            [],
            $this->getMethod($actionMeta),
            $request->attributes->get('_format', 'json')
        );
        $config->setLimit($this->getLimit($request, $helper));

        return $config;
    }

    /**
     * Get the data list of the form config.
     */
    protected function getDataList(
        Request $request,
        TranslatorInterface $translator,
        ConverterRegistryInterface $converterRegistry,
        FormConfigListInterface $config
    ): array {
        $converter = $converterRegistry->get($config->getConverter());
        $dataList = $converter->convert((string) $request->getContent());
        $limit = $config->getLimit();

        try {
            $dataList = $config->findList($dataList);
        } catch (InvalidResourceException $e) {
            throw new InvalidResourceException($translator->trans('form_handler.results_field_required', [], 'KlipperResource'));
        }

        if (null !== $limit && \count($dataList) > $limit) {
            $msg = $translator->trans('form_handler.size_exceeds', [
                '{{ limit }}' => $limit,
            ], 'KlipperResource');

            throw new InvalidResourceException(sprintf($msg, $limit));
        }

        return $dataList;
    }

    /**
     * Get the object instance or the classname.
     *
     * @return object|string
     */
    protected function getObjectOrClass(
        Request $request,
        ControllerHelper $helper,
        ConverterRegistryInterface $converterRegistry,
        DoctrineParamConverterExpressionLanguage $expressionLanguage,
        ObjectMetadataInterface $meta
    ) {
        $class = $meta->getClass();
        $domain = $helper->getDomain($class);
        $converter = $converterRegistry->get($request->attributes->get('_format', 'json'));
        $data = $converter->convert((string) $request->getContent());
        $classMeta = $domain->getObjectManager()->getClassMetadata($class);
        $fieldId = current($classMeta->getIdentifierFieldNames());
        $object = null;

        if (\is_array($data) && isset($data[$fieldId])) {
            $object = $this->getObject($request, $helper, $expressionLanguage, $domain, $data[$fieldId]);
        }

        return $object ?? $class;
    }

    /**
     * Get the object instance of id.
     *
     * @param int|string $id The id
     *
     * @throws NotFoundHttpException
     */
    protected function getObject(
        Request $request,
        ControllerHelper $helper,
        DoctrineParamConverterExpressionLanguage $expressionLanguage,
        DomainInterface $domain,
        $id
    ): object {
        $repo = $domain->getRepository();

        if (null !== $expr = $request->attributes->get('_repository_expr')) {
            $object = $expressionLanguage->evaluate($expr, [
                'repository' => $repo,
                'id' => $id,
            ]);
        } else {
            $defaultMet = $repo instanceof TranslatableRepositoryInterface ? 'findOneTranslatedById' : 'findOneById';
            $method = $request->attributes->get('_repository_method', $defaultMet);

            if ($repo instanceof TranslatableRepositoryInterface) {
                $object = $repo->{$method}($id, RequestUtil::getLanguage($request));
            } else {
                $object = $repo->{$method}($id);
            }
        }

        if (null === $object) {
            throw $helper->createNotFoundException();
        }

        return $object;
    }

    /**
     * @param string|string[] $actions The action names
     * @param null|int|string $id      The id
     */
    protected function checkSecurity(Request $request, ControllerHelper $helper, $actions, $id = null): void
    {
        $actions = (array) $actions;
        $class = $request->attributes->get('_action_class');
        $subject = null !== $id ? [$class, $id] : $class;

        foreach ($actions as $action) {
            if (class_exists($class) && !$helper->isGranted(new PermVote($action), $subject)) {
                throw $helper->createAccessDeniedException();
            }
        }
    }

    protected function addGroups(Request $request, array $groups): void
    {
        $groups = array_unique(array_merge(
            $groups,
            $request->attributes->get('_view_groups', [])
        ));

        if (\in_array('Views', $groups, true)
            && !\in_array(ViewGroups::VIEWS_DETAILS, $groups, true)
            && RequestHeaderUtil::getBoolean($request, RequestHeaders::VIEWS_DETAILS)
        ) {
            $groups[] = ViewGroups::VIEWS_DETAILS;
        }

        $request->attributes->set('_view_groups', $groups);
    }
}
