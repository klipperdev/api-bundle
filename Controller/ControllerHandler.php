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

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ObjectRepository;
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
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\ErrorActionListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\ErrorListActionListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\SuccessActionListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Listener\SuccessListActionListenerInterface;
use Klipper\Bundle\ApiBundle\Controller\Action\Traits\NewOptionsInterface;
use Klipper\Bundle\ApiBundle\Exception\InvalidArgumentException;
use Klipper\Bundle\ApiBundle\Representation\Errors;
use Klipper\Bundle\ApiBundle\Representation\Result;
use Klipper\Bundle\ApiBundle\Representation\ResultErrors;
use Klipper\Bundle\ApiBundle\Representation\ResultList;
use Klipper\Bundle\ApiBundle\RequestHeaders;
use Klipper\Bundle\ApiBundle\Util\CallableUtil;
use Klipper\Bundle\ApiBundle\View\Transformer\GetViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\Transformer\PostPaginateViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\Transformer\PostViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\Transformer\PrePaginateViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\Transformer\PreViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\Transformer\ViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\View;
use Klipper\Component\DoctrineExtensionsExtra\Model\Traits\TranslatableInterface;
use Klipper\Component\DoctrineExtensionsExtra\Pagination\RequestPaginationQuery;
use Klipper\Component\DoctrineExtensionsExtra\Representation\Pagination;
use Klipper\Component\DoctrineExtensionsExtra\Representation\PaginationInterface;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\HttpFoundation\Util\RequestUtil;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Component\Resource\Handler\DomainFormConfigList;
use Klipper\Component\Resource\Handler\FormConfigInterface;
use Klipper\Component\Resource\Handler\FormConfigListInterface;
use Klipper\Component\Resource\Handler\FormHandlerInterface;
use Klipper\Component\Resource\Model\SoftDeletableInterface;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceListInterface;
use Klipper\Component\Translation\ExceptionTranslatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Controller handler.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ControllerHandler
{
    protected DomainManagerInterface $domainManager;

    protected FormHandlerInterface $formHandler;

    protected ExceptionTranslatorInterface $exceptionTranslator;

    protected RequestStack $requestStack;

    protected array $viewTransformers = [];

    protected array $tempViewTransformers = [];

    protected ?View $tempView = null;

    /**
     * @param DomainManagerInterface       $domainManager       The domain manager
     * @param FormHandlerInterface         $formHandler         The form handler
     * @param ExceptionTranslatorInterface $exceptionTranslator The exception translator
     * @param RequestStack                 $requestStack        The request stack
     */
    public function __construct(
        DomainManagerInterface $domainManager,
        FormHandlerInterface $formHandler,
        ExceptionTranslatorInterface $exceptionTranslator,
        RequestStack $requestStack
    ) {
        $this->domainManager = $domainManager;
        $this->formHandler = $formHandler;
        $this->exceptionTranslator = $exceptionTranslator;
        $this->requestStack = $requestStack;
    }

    /**
     * Add the view transformer.
     *
     * @param callable|ViewTransformerInterface $transformer          The view transformer
     * @param null|string                       $transformerInterface The interface of the transformer if the
     *                                                                callable isn't a class
     * @param bool                              $permanent            Check if the view transformer is permanent
     *
     * @return static
     */
    public function addViewTransformer($transformer, ?string $transformerInterface = null, bool $permanent = false): self
    {
        $interfaces = class_implements($transformer);

        if (!$transformer instanceof ViewTransformerInterface && !\is_callable($transformer)) {
            throw new InvalidArgumentException('The view transformer must be an callable or an instance of ViewTransformerInterface');
        }

        if (empty($interfaces)) {
            if (null === $transformerInterface) {
                throw new InvalidArgumentException('The view transformer requires the $transformerInterface argument if it is not a class');
            }

            $interfaces = [$transformerInterface];
        }

        foreach ($interfaces as $interface) {
            if ($permanent) {
                $this->viewTransformers[$interface][] = $transformer;
            } else {
                $this->tempViewTransformers[$interface][] = $transformer;
            }
        }

        return $this;
    }

    /**
     * Set the view.
     *
     * @param View $view The view
     */
    public function setView(View $view): View
    {
        return $this->tempView = $view;
    }

    /**
     * Reset the non permanent view transformers.
     *
     * @return static
     */
    public function reset(): self
    {
        $this->tempViewTransformers = [];
        $this->tempView = null;

        return $this;
    }

    /**
     * Paginate doctrine orm query.
     *
     * @param Query|QueryBuilder $query The doctrine orm query
     */
    public function paginate($query): PaginationInterface
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        foreach ($this->getViewTransformers(PrePaginateViewTransformerInterface::class) as $transformer) {
            CallableUtil::call($transformer, 'prePaginate', [$query]);
        }

        $paginator = new Paginator($query);
        $results = $paginator->getIterator()->getArrayCopy();
        $size = $paginator->count();

        foreach ($this->getViewTransformers(PreViewTransformerInterface::class) as $transformer) {
            $results = CallableUtil::call($transformer, 'preView', [$results, $size]);
        }

        foreach ($this->getViewTransformers(GetViewTransformerInterface::class) as $transformer) {
            foreach ($results as $i => $result) {
                $results[$i] = CallableUtil::call($transformer, 'getView', [$result]);
            }
        }

        foreach ($this->getViewTransformers(PostViewTransformerInterface::class) as $transformer) {
            $results = CallableUtil::call($transformer, 'postView', [$results, $size]);
        }

        $pagination = new Pagination(
            $results,
            (int) $query->getHint(RequestPaginationQuery::HINT_PAGE_NUMBER),
            $query->getMaxResults(),
            (int) ceil($size / $query->getMaxResults()),
            $size
        );

        foreach ($this->getViewTransformers(PostPaginateViewTransformerInterface::class) as $transformer) {
            $pagination = CallableUtil::call($transformer, 'postPaginate', [$pagination]);
        }

        $this->tempViewTransformers = [];

        return $pagination;
    }

    /**
     * Paginate doctrine orm query and return a view.
     *
     * @param Query|QueryBuilder $query The doctrine orm query
     */
    public function views($query): View
    {
        return $this->getView($this->paginate($query));
    }

    /**
     * Handler to view an object.
     *
     * @param array|object $object The object instance or array
     */
    public function view($object): View
    {
        foreach ($this->getViewTransformers(GetViewTransformerInterface::class) as $transformer) {
            $object = CallableUtil::call($transformer, 'getView', [$object]);
        }

        return $this->getView($object);
    }

    /**
     * Handler to create and persist an object.
     *
     * @param Create $action The create action
     */
    public function create(Create $action): View
    {
        return $this->persist(Response::HTTP_CREATED, $action);
    }

    /**
     * Handler to create and persist a list of objects.
     *
     * @param Creates $action The creates action
     */
    public function creates(Creates $action): View
    {
        return $this->persists(Response::HTTP_CREATED, $action);
    }

    /**
     * Handler to update and persist an object.
     *
     * @param Update $action The update action
     */
    public function update(Update $action): View
    {
        return $this->persist(Response::HTTP_OK, $action);
    }

    /**
     * Handler to update and persist a list of objects.
     *
     * @param Updates $action The updates action
     */
    public function updates(Updates $action): View
    {
        return $this->persists(Response::HTTP_OK, $action);
    }

    /**
     * Handler to create or update and persist an object.
     *
     * @param Upsert $action The upsert action
     */
    public function upsert(Upsert $action): View
    {
        $code = \is_object($action->getObject()) ? Response::HTTP_OK : Response::HTTP_CREATED;

        return $this->persist($code, $action);
    }

    /**
     * Handler to create or update and persist a list of objects.
     *
     * @param Upserts $action The upserts action
     */
    public function upserts(Upserts $action): View
    {
        return $this->persists(Response::HTTP_OK, $action);
    }

    /**
     * Handler to delete an object.
     *
     * - Returns 200 OK when the object is deleted definitively and return object (with or without soft delete)
     * - Returns 204 NO CONTENT when the object is deleted definitively and return empty body
     * - Returns 202 ACCEPTED when the object is soft deleted and return empty body
     *
     * @param Delete $action The delete action
     */
    public function delete(Delete $action): View
    {
        $object = $action->getObject();
        $returnObject = $action->hasReturnedObject();
        $request = $this->getRequest();
        $domain = $this->domainManager->get(null !== $object ? ClassUtils::getClass($object) : '');
        $softDelete = !$this->isForceDelete();
        $code = $softDelete && $object instanceof SoftDeletableInterface ? Response::HTTP_ACCEPTED : null;
        $code = $code ?? ($returnObject ? Response::HTTP_OK : Response::HTTP_NO_CONTENT);

        if ($object instanceof TranslatableInterface && RequestUtil::hasRequestLanguage($request)) {
            $object->removeTranslationFields(RequestUtil::getRequestLanguage($request));
            $res = $domain->update($object);
        } else {
            $res = $domain->delete($object, $softDelete);
        }

        if ($res->isValid()) {
            $data = $returnObject ? $res->getRealData() : null;

            if ($returnObject) {
                foreach ($this->getViewTransformers(GetViewTransformerInterface::class) as $transformer) {
                    $data = CallableUtil::call($transformer, 'getView', [$data]);
                }
            }
        } else {
            $code = Response::HTTP_BAD_REQUEST;
            $data = $this->mergeAllErrors($res);
        }

        return $this->getView($data, $code);
    }

    /**
     * Handler to delete a list of objects.
     *
     * - Returns 200 OK when the objects are deleted definitively and return objects (with or without soft delete)
     * - Returns 202 ACCEPTED when the objects are soft deleted and return empty body
     *
     * @param Deletes $action The deletes action
     */
    public function deletes(Deletes $action): View
    {
        $objects = $action->getObjects();
        $returnObject = $action->hasReturnedObject();

        if (empty($objects)) {
            return $this->getView(null, Response::HTTP_NO_CONTENT);
        }

        $objects = array_values($objects);
        $current = current($objects);

        if (!\is_object($current)) {
            throw new InvalidArgumentException('The values must be objects');
        }

        $request = $this->getRequest();
        $domain = $this->domainManager->get(ClassUtils::getClass($current));
        $softDelete = !$this->isForceDelete();
        $code = $softDelete && $current instanceof SoftDeletableInterface
            ? Response::HTTP_ACCEPTED
            : Response::HTTP_OK;
        $actionUpdate = RequestUtil::hasRequestLanguage($request)
            && $current instanceof TranslatableInterface;
        $updates = [];
        $deletes = [];

        foreach ($objects as $object) {
            if (ClassUtils::getClass($object) !== $domain->getClass()) {
                throw new InvalidArgumentException('The objects must be the same class');
            }

            if ($actionUpdate) {
                /* @var TranslatableInterface $object */
                $object->removeTranslationFields(RequestUtil::getRequestLanguage($request));
                $updates[] = $object;
            } else {
                $deletes[] = $object;
            }
        }

        if (!empty($updates)) {
            $res = $domain->updates($deletes, $this->isTransactional());
            $data = $this->formatResultList($res, $returnObject);
        } else {
            $res = $domain->deletes($deletes, $softDelete, $this->isTransactional());
            $data = $this->formatResultList($res, $returnObject);
        }

        return $this->getView($data, $code);
    }

    /**
     * Handler to undelete an object.
     *
     * Best used with the PUT method.
     *
     * - Returns 200 OK when the object is undeleted
     * - Returns 204 NO CONTENT when the object is undeleted and return empty body
     *
     * @param Undelete $action The undelete action
     */
    public function undelete(Undelete $action): View
    {
        $identifier = $action->getIdentifier();
        $class = $action->getClass();
        $returnObject = $action->hasReturnedObject();
        $class = \is_object($identifier) ? ClassUtils::getClass($identifier) : $class;
        $domain = $this->domainManager->get($class);
        $code = Response::HTTP_OK;

        $res = $domain->undelete($identifier);

        if ($res->isValid()) {
            $data = $returnObject ? $res->getRealData() : null;

            if ($returnObject) {
                foreach ($this->getViewTransformers(GetViewTransformerInterface::class) as $transformer) {
                    $data = CallableUtil::call($transformer, 'getView', [$data]);
                }
            }
        } else {
            $code = Response::HTTP_BAD_REQUEST;
            $data = $this->mergeAllErrors($res);
        }

        return $this->getView($data, $code);
    }

    /**
     * Handler to delete a list of objects.
     *
     * - Returns 200 OK when the objects are deleted definitively and return objects (with or without soft delete)
     * - Returns 204 NO CONTENT when the objects are deleted definitively and return empty body
     * - Returns 202 ACCEPTED when the objects are soft deleted and return empty body
     *
     * @param Undeletes $action The undeletes action
     */
    public function undeletes(Undeletes $action): View
    {
        $identifiers = $action->getIdentifiers();
        $class = $action->getClass();
        $returnObject = $action->hasReturnedObject();

        if (empty($identifiers)) {
            return $this->getView(null, Response::HTTP_NO_CONTENT);
        }

        $identifiers = array_values($identifiers);
        $class = !empty($identifiers) && \is_object($identifiers[0])
            ? ClassUtils::getClass($identifiers[0])
            : $class;
        $domain = $this->domainManager->get($class);
        $res = $domain->undeletes($identifiers);
        $code = $res->hasErrors()
            ? Response::HTTP_BAD_REQUEST
            : Response::HTTP_OK;
        $data = $this->formatResultList($res, $returnObject);

        return $this->getView($data, $code);
    }

    /**
     * Check if the request requires a definitively delete.
     */
    public function isForceDelete(): bool
    {
        $request = $this->getRequest();

        return null !== $request
            && (bool) $request->headers->get(RequestHeaders::FORCE_DELETE, false);
    }

    /**
     * Check if the request requires is in transactional mode.
     */
    public function isTransactional(): bool
    {
        $request = $this->getRequest();

        return null !== $request
            && (bool) $request->headers->get(RequestHeaders::TRANSACTIONAL, true);
    }

    /**
     * Merge all constraint errors in resource.
     *
     * @param ResourceInterface $resource The resource
     */
    public function mergeAllErrors(ResourceInterface $resource): ResultErrors
    {
        return $this->mergeAllConstraintErrors($resource->getErrors());
    }

    /**
     * Merge all constraint errors in resource.
     *
     * @param ConstraintViolationListInterface $constraintErrors The constraint violation list
     */
    public function mergeAllConstraintErrors(ConstraintViolationListInterface $constraintErrors): ResultErrors
    {
        $errors = new Errors();

        /** @var ConstraintViolation $error */
        foreach ($constraintErrors as $error) {
            if (null !== $propertyPath = $error->getPropertyPath()) {
                if (!$errors->hasChild($propertyPath)) {
                    $errors->addChild($propertyPath, new Errors());
                }

                $errors->getChild($propertyPath)->addError($error->getMessage());
            } else {
                $errors->addError($error->getMessage());
            }
        }

        return $this->formatDataErrors($errors);
    }

    /**
     * Merge all constraint errors in form errors.
     *
     * @param ResourceInterface $resource The resource
     */
    public function mergeAllFormErrors(ResourceInterface $resource): ResultErrors
    {
        /** @var ConstraintViolation $err */
        foreach ($resource->getErrors() as $err) {
            $error = new FormError($err->getMessage(), $err->getMessageTemplate(), $err->getParameters(), $err->getPlural(), $err->getInvalidValue());
            $form = $err->getPropertyPath() && $resource->getData()->has($err->getPropertyPath())
                ? $resource->getData()->get($err->getPropertyPath())
                : $resource->getData();
            $form->addError($error);
        }

        return $this->formatDataErrors($this->extractFormErrors($resource->getData()));
    }

    /**
     * Format the response for form errors.
     *
     * @param FormInterface $form The invalid form
     */
    public function formatFormErrors(FormInterface $form): ResultErrors
    {
        return $this->formatDataErrors($this->extractFormErrors($form));
    }

    /**
     * Create the view for invalid form.
     *
     * @param FormInterface $form    The invalid form
     * @param array         $headers The response headers
     */
    public function createViewFormErrors(FormInterface $form, array $headers = []): View
    {
        $code = Response::HTTP_BAD_REQUEST;
        $data = $this->formatFormErrors($form);

        return View::create($data, $code, $headers);
    }

    /**
     * Create the view for invalid constraint.
     *
     * @param ConstraintViolationException $exception The constraint violation exception
     * @param array                        $headers   The response headers
     */
    public function createViewConstraintErrors(ConstraintViolationException $exception, array $headers = []): View
    {
        $code = Response::HTTP_BAD_REQUEST;
        $data = $this->mergeAllConstraintErrors($exception->getConstraintViolations());

        return View::create($data, $code, $headers);
    }

    /**
     * Check if the class is managed by the object manager.
     */
    public function hasDomain(string $class): bool
    {
        return $this->domainManager->has($class);
    }

    /**
     * Get the domain for the managed class by the object manager.
     */
    public function getDomain(string $class): DomainInterface
    {
        return $this->domainManager->get($class);
    }

    /**
     * Create an instance of the managed class by the object manager.
     */
    public function newInstance(string $class, array $options = []): object
    {
        return $this->getDomain($class)->newInstance($options);
    }

    /**
     * Get the object repository of the managed class by the object manager.
     */
    public function getRepository(string $class): ObjectRepository
    {
        return $this->getDomain($class)->getRepository();
    }

    /**
     * Process form for one object instance (create and submit form).
     *
     * @param array|object $object The object instance
     */
    public function processForm(FormConfigInterface $config, $object): FormInterface
    {
        return $this->formHandler->processForm($config, $object);
    }

    /**
     * Process form for one object instance (create and submit form).
     *
     * @param array[]|object[] $objects The list of object instance
     *
     * @return FormInterface[]
     */
    public function processForms(FormConfigListInterface $config, array $objects = []): array
    {
        return $this->formHandler->processForms($config, $objects);
    }

    /**
     * Get the default limit. If the value is null, then there is not limit of quantity of rows.
     */
    public function getFormDefaultLimit(): ?int
    {
        return $this->formHandler->getDefaultLimit();
    }

    /**
     * Get the max limit. If the value is null, then there is not limit of quantity of rows.
     */
    public function getFormMaxLimit(): ?int
    {
        return $this->formHandler->getMaxLimit();
    }

    /**
     * Handler to create/update and persist an object.
     *
     * @param int             $code   The status code
     * @param ActionInterface $action The controller action
     */
    protected function persist(int $code, ActionInterface $action): View
    {
        $domain = $this->domainManager->get($action->getClass());

        try {
            $newOptions = $action instanceof NewOptionsInterface ? $action->getNewOptions() : [];
            $object = $action instanceof Update || $action instanceof Upsert ? $action->getObject() : null;
            $object = \is_object($object) ? $object : $domain->newInstance($newOptions);

            $form = $this->formHandler->processForm($action, $object);
            $actionMethod = lcfirst(substr(strrchr(\get_class($action), '\\'), 1));
            /** @var ResourceInterface $res */
            $res = $domain->{$actionMethod}($form);

            if ($res->isValid()) {
                foreach ($action->getListeners(SuccessActionListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onSingleSuccess', [$res]);
                }

                $data = $res->getRealData();

                foreach ($this->getViewTransformers(GetViewTransformerInterface::class) as $transformer) {
                    $data = CallableUtil::call($transformer, 'getView', [$data]);
                }
            } else {
                foreach ($action->getListeners(ErrorActionListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onSingleError', [$res]);
                }

                $code = Response::HTTP_BAD_REQUEST;
                $data = $this->mergeAllFormErrors($res);
            }
        } catch (\Throwable $e) {
            throw new BadRequestHttpException($this->exceptionTranslator->transDomainThrowable($e), $e);
        }

        return $this->getView($data, $code);
    }

    /**
     * Handler to create/update and persist an object.
     *
     * @param int                 $code   The status code
     * @param ActionListInterface $action The controller action
     */
    protected function persists(int $code, ActionListInterface $action): View
    {
        $class = $action->getClass();
        $domain = $this->domainManager->get($class);
        $config = new DomainFormConfigList($domain, $action->getType(), $action->getOptions(), $action->getMethod());

        $config->setSubmitClearMissing($action->getSubmitClearMissing());
        $config->setConverter($action->getConverter());
        $config->setLimit($action->getLimit());
        $config->setTransactional($action->isTransactional() ?? $this->isTransactional());
        $config->setDefaultValueOptions($action instanceof NewOptionsInterface ? $action->getNewOptions() : []);
        $config->setCreation($action instanceof Creates);
        $config->setIdentifier(current($domain->getObjectManager()->getClassMetadata($class)->getIdentifier()));

        try {
            $forms = $this->formHandler->processForms($config);
            $actionMethod = lcfirst(substr(strrchr(\get_class($action), '\\'), 1));
            /** @var ResourceListInterface $res */
            $res = $domain->{$actionMethod}($forms, !$config->isTransactional());

            if (!$res->hasErrors()) {
                foreach ($action->getListeners(SuccessListActionListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onListSuccess', [$res]);
                }
            } else {
                foreach ($action->getListeners(ErrorListActionListenerInterface::class) as $listener) {
                    CallableUtil::call($listener, 'onListError', [$res]);
                }
            }

            $code = $res->hasErrors() ? Response::HTTP_BAD_REQUEST : $code;
            $data = $this->formatResultList($res);
        } catch (\Throwable $e) {
            throw new BadRequestHttpException($this->exceptionTranslator->transDomainThrowable($e), $e);
        }

        return $this->getView($data, $code);
    }

    /**
     * Format the resource list.
     *
     * @param ResourceListInterface $resourceList The resource list
     * @param bool                  $addRecords   Check if the records must be included
     */
    protected function formatResultList(ResourceListInterface $resourceList, bool $addRecords = true): ResultList
    {
        $resultList = new ResultList($resourceList);

        foreach ($resourceList->all() as $result) {
            $realData = $result->getRealData();
            $resultData = null;
            $resultErrors = null;

            if ($addRecords) {
                $resultData = $realData;

                foreach ($this->getViewTransformers(GetViewTransformerInterface::class) as $transformer) {
                    $resultData = CallableUtil::call($transformer, 'getView', [$resultData]);
                }
            }

            if (!$result->isValid()) {
                $resultErrors = $this->mergeAllFormErrors($result);
            }

            if ($addRecords || !$result->isValid()) {
                $resultList->addRecord(new Result($result, $resultData, $resultErrors));
            }
        }

        return $resultList;
    }

    /**
     * Format the response for errors.
     *
     * @param Errors $err The errors
     */
    protected function formatDataErrors(Errors $err): ResultErrors
    {
        $data = new ResultErrors(
            $this->exceptionTranslator->trans(Response::$statusTexts[Response::HTTP_BAD_REQUEST])
        );

        foreach ($err->getErrors() as $error) {
            $data->addError($error);
        }

        foreach ($err->getChildren() as $name => $child) {
            $data->addChild($name, $child);
        }

        return $data;
    }

    /**
     * Extract the error messages of form.
     *
     * @param FormInterface $form The form
     */
    protected function extractFormErrors(FormInterface $form): Errors
    {
        $errors = new Errors();

        if ($form->getErrors()->count() > 0) {
            foreach ($this->getFieldErrors($form->getErrors()) as $formError) {
                $errors->addError($formError->getMessage());
            }
        }

        foreach ($form->all() as $name => $child) {
            if (\count($child->getErrors(true)) > 0) {
                $errors->addChild($name, $this->extractFormErrors($child));
            }
        }

        return $errors;
    }

    /**
     * Get the error messages of field.
     *
     * @param FormErrorIterator $errors The field errors
     *
     * @return FormError[]|FormErrorIterator[]
     */
    protected function getFieldErrors(FormErrorIterator $errors): array
    {
        $size = $errors->count();
        $list = [];

        for ($i = 0; $i < $size; ++$i) {
            $list[] = $errors->offsetGet($i);
        }

        return $list;
    }

    /**
     * Get all view transformers.
     *
     * @param string $interface The interface
     *
     * @return callable[]|ViewTransformerInterface[]
     */
    protected function getViewTransformers(string $interface): iterable
    {
        return array_merge($this->viewTransformers[$interface] ?? [], $this->tempViewTransformers[$interface] ?? []);
    }

    /**
     * Get the view.
     *
     * @param null|mixed $data       The data of view
     * @param null|int   $statusCode The status code of view
     */
    private function getView($data = null, ?int $statusCode = null): View
    {
        $view = $this->tempView ?? View::create();

        if (null === $view->getData()) {
            $view->setData($data);
        }

        if (null === $view->getStatusCode()) {
            $view->setStatusCode($statusCode);
        }

        $this->reset();

        return $view;
    }

    /**
     * Get the request.
     */
    private function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
