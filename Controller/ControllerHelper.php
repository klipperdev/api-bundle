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
use Klipper\Bundle\ApiBundle\Representation\ResultErrors;
use Klipper\Bundle\ApiBundle\View\Transformer\ViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\View;
use Klipper\Bundle\ApiBundle\View\ViewHandler;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Component\Resource\ResourceInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ControllerHelper
{
    private ControllerHandler $controllerHandler;

    private ViewHandler $viewHandler;

    public function __construct(ControllerHandler $controllerHandler, ViewHandler $viewHandler)
    {
        $this->controllerHandler = $controllerHandler;
        $this->viewHandler = $viewHandler;
    }

    /**
     * Set the view.
     */
    public function setView(View $view): View
    {
        return $this->controllerHandler->setView($view);
    }

    /**
     * Create and set the view.
     *
     * @param mixed $data
     */
    public function createView($data = null, ?int $statusCode = null, array $headers = []): View
    {
        return $this->setView(View::create($data, $statusCode, $headers));
    }

    /**
     * Handle the view.
     *
     * @param View $view The view
     *
     * @final
     */
    public function handleView(View $view): Response
    {
        return $this->viewHandler->handle($view);
    }

    /**
     * Add the view transformer.
     *
     * @param callable|ViewTransformerInterface $viewTransformer      The view transformer
     * @param null|string                       $transformerInterface The interface of the transformer
     */
    public function addViewTransformer($viewTransformer, ?string $transformerInterface = null): self
    {
        $this->controllerHandler->addViewTransformer($viewTransformer, $transformerInterface);

        return $this;
    }

    /**
     * Create the view of form errors.
     */
    public function createViewFormErrors(FormInterface $form, array $headers = []): View
    {
        return $this->controllerHandler->createViewFormErrors($form, $headers);
    }

    /**
     * Create the view of constraint errors.
     */
    public function createViewConstraintErrors(
        ConstraintViolationException $exception,
        array $headers = []
    ): View {
        return $this->controllerHandler->createViewConstraintErrors($exception, $headers);
    }

    /**
     * Merge all constraint errors in resource.
     */
    public function mergeAllErrors(ResourceInterface $resource): ResultErrors
    {
        return $this->controllerHandler->mergeAllErrors($resource);
    }

    /**
     * Merge all constraint errors in form errors.
     *
     * @param ResourceInterface $resource The resource
     */
    public function mergeAllFormErrors(ResourceInterface $resource): ResultErrors
    {
        return $this->controllerHandler->mergeAllFormErrors($resource);
    }

    /**
     * Create a BadRequestHttpException.
     */
    public function createBadRequestException(
        string $message = 'Bad Request',
        \Throwable $previous = null
    ): BadRequestHttpException {
        if (!class_exists(BadRequestHttpException::class)) {
            throw new \LogicException('You can not use the "createBadRequestException" method if the HTTP Kernel component is not available. Try running "composer require symfony/http-kernel".');
        }

        return new BadRequestHttpException($message, $previous);
    }

    /**
     * Create an AccessDeniedException.
     */
    public function createAccessDeniedException(
        string $message = 'Access Denied.',
        ?\Throwable $previous = null
    ): AccessDeniedException {
        if (!class_exists(AccessDeniedException::class)) {
            throw new \LogicException('You can not use the "createAccessDeniedException" method if the Security component is not available. Try running "composer require symfony/security-bundle".');
        }

        return new AccessDeniedException($message, $previous);
    }

    /**
     * Create a NotFoundHttpException.
     */
    public function createNotFoundException(
        string $message = 'Not Found',
        ?\Throwable $previous = null
    ): NotFoundHttpException {
        if (!class_exists(NotFoundHttpException::class)) {
            throw new \LogicException('You can not use the "createNotFoundException" method if the HTTP Kernel component is not available. Try running "composer require symfony/http-kernel".');
        }

        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Create the object.
     */
    public function create(Create $action): Response
    {
        $view = $this->controllerHandler->create($action);

        return $this->handleView($view);
    }

    /**
     * Create a list of objects.
     */
    public function creates(Creates $action): Response
    {
        $view = $this->controllerHandler->creates($action);

        return $this->handleView($view);
    }

    /**
     * Update the object.
     */
    public function update(Update $action): Response
    {
        if (null === $action->getObject()) {
            throw $this->createNotFoundException();
        }

        $view = $this->controllerHandler->update($action);

        return $this->handleView($view);
    }

    /**
     * Update a list of objects.
     */
    public function updates(Updates $action): Response
    {
        $view = $this->controllerHandler->updates($action);

        return $this->handleView($view);
    }

    /**
     * Create or update and persist an object.
     */
    public function upsert(Upsert $action): Response
    {
        if (null === $action->getObject()) {
            throw $this->createNotFoundException();
        }

        $view = $this->controllerHandler->upsert($action);

        return $this->handleView($view);
    }

    /**
     * Create or update a list of objects.
     */
    public function upserts(Upserts $action): Response
    {
        $view = $this->controllerHandler->upserts($action);

        return $this->handleView($view);
    }

    /**
     * Delete the object.
     */
    public function delete(Delete $action): Response
    {
        if (null === $action->getObject()) {
            throw $this->createNotFoundException();
        }

        $view = $this->controllerHandler->delete($action);

        return $this->handleView($view);
    }

    /**
     * Handler to delete a list of objects.
     */
    public function deletes(Deletes $action): Response
    {
        $view = $this->controllerHandler->deletes($action);

        return $this->handleView($view);
    }

    /**
     * Undelete the object.
     */
    public function undelete(Undelete $action): Response
    {
        $view = $this->controllerHandler->undelete($action);

        return $this->handleView($view);
    }

    /**
     * Undelete a list of objects.
     *
     * - Returns 200 OK when the objects are deleted definitively and return objects (with or without soft delete)
     * - Returns 204 NO CONTENT when the objects are deleted definitively and return empty body
     * - Returns 202 ACCEPTED when the objects are soft deleted and return empty body
     */
    public function undeletes(Undeletes $action): Response
    {
        $view = $this->controllerHandler->undeletes($action);

        return $this->handleView($view);
    }

    /**
     * View the object.
     *
     * @param array|object $object The object
     */
    public function view($object): Response
    {
        $view = $this->controllerHandler->view($object);

        return $this->handleView($view);
    }

    /**
     * View the list of objects.
     *
     * @param Query|QueryBuilder $query The doctrine orm query
     */
    public function views($query): Response
    {
        $view = $this->controllerHandler->views($query);

        return $this->handleView($view);
    }
}
