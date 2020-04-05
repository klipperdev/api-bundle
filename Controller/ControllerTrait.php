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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Filter\SQLFilter;
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
use Klipper\Bundle\ApiBundle\View\Transformer\ViewTransformerInterface;
use Klipper\Bundle\ApiBundle\View\View;
use Klipper\Component\Content\Downloader\DownloaderInterface;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Component\Resource\Handler\FormConfigInterface;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Security\Model\UserInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller trait.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 *
 * @property ContainerInterface $container
 */
trait ControllerTrait
{
    /**
     * Returns a BadRequestHttpException.
     *
     * This will result in a 400 response code. Usage example:
     *
     *     throw $this->createBadRequestException('Bad request!');
     *
     * @param string          $message  The message
     * @param null|\Exception $previous The previous exception
     *
     * @final
     */
    protected function createBadRequestException(
        string $message = 'Bad Request',
        \Exception $previous = null
    ): BadRequestHttpException {
        if (!class_exists(BadRequestHttpException::class)) {
            throw new \LogicException('You can not use the "createBadRequestException" method if the HTTP Kernel component is not available. Try running "composer require symfony/http-kernel".');
        }

        return new BadRequestHttpException($message, $previous);
    }

    /**
     * Returns a UnprocessableEntityHttpException.
     *
     * This will result in a 422 response code. Usage example:
     *
     *     throw $this->createUnprocessableEntityException('Unprocessable entity!');
     *
     * @param string          $message  The message
     * @param null|\Exception $previous The previous exception
     *
     * @final
     */
    protected function createUnprocessableEntityException(
        string $message = 'Unprocessable Entity',
        \Exception $previous = null
    ): UnprocessableEntityHttpException {
        if (!class_exists(UnprocessableEntityHttpException::class)) {
            throw new \LogicException('You can not use the "createUnprocessableEntityException" method if the HTTP Kernel component is not available. Try running "composer require symfony/http-kernel".');
        }

        return new UnprocessableEntityHttpException($message, $previous);
    }

    /**
     * Convert the string content to array.
     *
     * @param string $content The content
     * @param string $format  The content format
     */
    protected function convert(string $content, string $format = 'json'): array
    {
        if (!$this->container->has('klipper_resource.converter_registry')) {
            throw new \LogicException('The ResourceBundle is not registered in your application. Try running "composer require klipper/resource-bundle".');
        }

        return $this->container->get('klipper_resource.converter_registry')->get($format)->convert($content);
    }

    /**
     * Get the resource domain.
     *
     * @param string $class The class name
     */
    protected function getDomain(string $class): DomainInterface
    {
        if (!$this->container->has('klipper_resource.domain_manager')) {
            throw new \LogicException('The ResourceBundle is not registered in your application. Try running "composer require klipper/resource-bundle".');
        }

        return $this->container->get('klipper_resource.domain_manager')->get($class);
    }

    /**
     * Get the entity repository.
     *
     * @param string $class The class name
     */
    protected function getRepository(string $class): EntityRepository
    {
        return $this->getDomain($class)->getRepository();
    }

    /**
     * Create the doctrine query builder.
     *
     * @param string      $class   The class name
     * @param string      $alias   The alias of class name
     * @param null|string $indexBy The index by
     */
    protected function createQueryBuilder(string $class, string $alias = 'o', string $indexBy = null): QueryBuilder
    {
        return $this->getDomain($class)->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Create the view of form errors.
     *
     * @param FormInterface $form    The form
     * @param array         $headers The headers
     */
    protected function createViewFormErrors(FormInterface $form, array $headers = []): View
    {
        return $this->container->get('klipper_api.controller_handler')
            ->createViewFormErrors($form, $headers)
        ;
    }

    /**
     * Create the view of constraint errors.
     *
     * @param ConstraintViolationException $exception The constraint violation exception
     * @param array                        $headers   The headers
     */
    protected function createViewConstraintErrors(
        ConstraintViolationException $exception,
        array $headers = []
    ): View {
        return $this->container->get('klipper_api.controller_handler')
            ->createViewConstraintErrors($exception, $headers)
        ;
    }

    /**
     * Merge all constraint errors in resource.
     *
     * @param ResourceInterface $resource The resource
     */
    protected function mergeAllErrors(ResourceInterface $resource): array
    {
        return $this->container->get('klipper_api.controller_handler')->mergeAllErrors($resource);
    }

    /**
     * Merge all constraint errors in form errors.
     *
     * @param ResourceInterface $resource The resource
     */
    protected function mergeAllFormErrors(ResourceInterface $resource): array
    {
        return $this->container->get('klipper_api.controller_handler')->mergeAllFormErrors($resource);
    }

    /**
     * Process form for one object instance (create and submit form).
     *
     * @param FormConfigInterface $config The form config
     * @param array|object        $object The object instance
     */
    protected function processForm(FormConfigInterface $config, $object): FormInterface
    {
        if (!$this->container->has('klipper_resource.form_handler')) {
            throw new \LogicException('The ResourceBundle is not registered in your application. Try running "composer require klipper/resource-bundle".');
        }

        return $this->container->get('klipper_resource.form_handler')->processForm($config, $object);
    }

    /**
     * Create the object.
     *
     * @param Create $action The create action
     */
    protected function create(Create $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->create($action);

        return $this->handleView($view);
    }

    /**
     * Create a list of objects.
     *
     * @param Creates $action The creates action
     */
    protected function creates(Creates $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->creates($action);

        return $this->handleView($view);
    }

    /**
     * Update the object.
     *
     * @param Update $action The update action
     */
    protected function update(Update $action): Response
    {
        if (null === $action->getObject()) {
            throw $this->createNotFoundException();
        }

        $view = $this->container->get('klipper_api.controller_handler')->update($action);

        return $this->handleView($view);
    }

    /**
     * Update a list of objects.
     *
     * @param Updates $action The updates action
     */
    protected function updates(Updates $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->updates($action);

        return $this->handleView($view);
    }

    /**
     * Create or update and persist an object.
     *
     * @param Upsert $action The upsert action
     */
    protected function upsert(Upsert $action): Response
    {
        if (null === $action->getObject()) {
            throw $this->createNotFoundException();
        }

        $view = $this->container->get('klipper_api.controller_handler')->upsert($action);

        return $this->handleView($view);
    }

    /**
     * Create or update a list of objects.
     *
     * @param Upserts $action The upserts action
     */
    protected function upserts(Upserts $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->upserts($action);

        return $this->handleView($view);
    }

    /**
     * Delete the object.
     *
     * @param Delete $action The delete action
     */
    protected function delete(Delete $action): Response
    {
        if (null === $action->getObject()) {
            throw $this->createNotFoundException();
        }

        $view = $this->container->get('klipper_api.controller_handler')->delete($action);

        return $this->handleView($view);
    }

    /**
     * Handler to delete a list of objects.
     *
     * @param Deletes $action The deletes action
     */
    protected function deletes(Deletes $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->deletes($action);

        return $this->handleView($view);
    }

    /**
     * Undelete the object.
     *
     * @param Undelete $action The undelete action
     */
    protected function undelete(Undelete $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->undelete($action);

        return $this->handleView($view);
    }

    /**
     * Undelete a list of objects.
     *
     * - Returns 200 OK when the objects are deleted definitively and return objects (with or without soft delete)
     * - Returns 204 NO CONTENT when the objects are deleted definitively and return empty body
     * - Returns 202 ACCEPTED when the objects are soft deleted and return empty body
     *
     * @param Undeletes $action The undeletes action
     */
    protected function undeletes(Undeletes $action): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->undeletes($action);

        return $this->handleView($view);
    }

    /**
     * View the object.
     *
     * @param array|object $object The object
     */
    protected function view($object): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->view($object);

        return $this->handleView($view);
    }

    /**
     * View the list of objects.
     *
     * @param Query|QueryBuilder $query The doctrine orm query
     */
    protected function views($query): Response
    {
        $view = $this->container->get('klipper_api.controller_handler')->views($query);

        return $this->handleView($view);
    }

    /**
     * Upload the file.
     *
     * @param string $uploader The uploader name
     */
    protected function upload(string $uploader): Response
    {
        if (!$this->container->has('klipper_content.uploader')) {
            throw new \LogicException('The ContentBundle is not registered in your application. Try running "composer require klipper/content-bundle".');
        }

        return $this->container->get('klipper_content.uploader')->upload($uploader);
    }

    /**
     * Download the file.
     *
     * @param null|string $path               The file path
     * @param null|string $contentDisposition The content disposition
     * @param array       $headers            The custom headers
     * @param string      $mode               The download mode
     *
     * @throws NotFoundHttpException When path is empty
     */
    protected function download(
        ?string $path,
        ?string $contentDisposition = null,
        array $headers = [],
        string $mode = DownloaderInterface::MODE_AUTO
    ): Response {
        if (null === $path) {
            throw $this->createNotFoundException();
        }

        if (!$this->container->has('klipper_content.downloader')) {
            throw new \LogicException('The ContentBundle is not registered in your application. Try running "composer require klipper/content-bundle".');
        }

        return $this->container->get('klipper_content.downloader')
            ->download($path, $contentDisposition, $headers, $mode)
        ;
    }

    /**
     * Optimize and download the image.
     *
     * @param null|string $path               The image path
     * @param null|string $contentDisposition The content disposition
     * @param array       $headers            The custom headers
     *
     * @throws NotFoundHttpException When path is empty
     */
    protected function downloadImage(?string $path, string $contentDisposition = null, array $headers = []): Response
    {
        return $this->download($path, $contentDisposition, $headers, DownloaderInterface::MODE_FORCE_IMAGE_MANIPULATOR);
    }

    /**
     * Returns true if the service id is defined.
     *
     * @param string $id The service id
     *
     * @final
     */
    protected function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return object The service
     *
     * @final
     */
    protected function get(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @param mixed $attributes The security attributes
     * @param mixed $subject    The security subject
     *
     * @throws \LogicException
     *
     * @final
     */
    protected function isGranted($attributes, $subject = null): bool
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attributes, $subject);
    }

    /**
     * Throws an exception unless the attributes are granted against the current authentication token and optionally
     * supplied subject.
     *
     * @param mixed  $attributes The security attributes
     * @param mixed  $subject    The security subject
     * @param string $message    The exception message
     *
     * @throws AccessDeniedException
     *
     * @final
     */
    protected function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->isGranted($attributes, $subject)) {
            $exception = $this->createAccessDeniedException($message);
            $exception->setAttributes($attributes);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @param string          $message  The message
     * @param null|\Exception $previous The previous exception
     *
     * @throws \LogicException If the Security component is not available
     *
     * @final
     */
    protected function createAccessDeniedException(
        string $message = 'Access Denied.',
        \Exception $previous = null
    ): AccessDeniedException {
        if (!class_exists(AccessDeniedException::class)) {
            throw new \LogicException('You can not use the "createAccessDeniedException" method if the Security component is not available. Try running "composer require symfony/security-bundle".');
        }

        return new AccessDeniedException($message, $previous);
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string          $message  The message
     * @param null|\Exception $previous The previous exception
     *
     * @final
     */
    protected function createNotFoundException(
        string $message = 'Not Found',
        \Exception $previous = null
    ): NotFoundHttpException {
        if (!class_exists(NotFoundHttpException::class)) {
            throw new \LogicException('You can not use the "createNotFoundException" method if the HTTP Kernel component is not available. Try running "composer require symfony/http-kernel".');
        }

        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     *
     * @final
     */
    protected function getUser(): ?UserInterface
    {
        $user = null;

        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        if (null !== ($token = $this->container->get('security.token_storage')->getToken())
                && $token->getUser() instanceof UserInterface) {
            $user = $token->getUser();
        }

        return $user;
    }

    /**
     * Dispatches a message to the bus.
     *
     * @param Envelope|object $message The message or the message pre-wrapped in an envelope
     *
     * @throws
     *
     * @final
     */
    protected function dispatchMessage($message): Envelope
    {
        if (!$this->container->has('message_bus')) {
            $message = class_exists(Envelope::class) ? 'You need to define the "messenger.default_bus" configuration option.' : 'Try running "composer require symfony/messenger".';

            throw new \LogicException('The message bus is not enabled in your application. '.$message);
        }

        return $this->container->get('message_bus')->dispatch($message);
    }

    /**
     * Add the view transformer.
     *
     * @param callable|ViewTransformerInterface $viewTransformer      The view transformer
     * @param null|string                       $transformerInterface The interface of the transformer if the
     *
     * @return static
     *
     * @final
     */
    protected function addViewTransformer($viewTransformer, ?string $transformerInterface = null): self
    {
        $this->container->get('klipper_api.controller_handler')
            ->addViewTransformer($viewTransformer, $transformerInterface)
        ;

        return $this;
    }

    /**
     * Set the view.
     *
     * @param View $view The view
     *
     * @final
     */
    protected function setView(View $view): View
    {
        return $this->container->get('klipper_api.controller_handler')->setView($view);
    }

    /**
     * Create and set the view.
     *
     * @param mixed $data
     * @param int   $statusCode
     *
     * @final
     */
    protected function createView($data = null, ?int $statusCode = null, array $headers = []): View
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
    protected function handleView(View $view): Response
    {
        return $this->container->get('klipper_api.view_handler')->handle($view);
    }

    /**
     * Disable the sql filters.
     *
     * @param string[] $filters The sql filter names
     * @param bool     $all     Force all SQL Filter
     *
     * @return string[]
     *
     * @final
     */
    protected function disableSqlFilters(array $filters = [], bool $all = false): array
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/doctrine-bundle".');
        }

        $em = $this->container->get('doctrine')->getManager();

        $filters = SqlFilterUtil::findFilters($em, $filters, $all);
        SqlFilterUtil::disableFilters($em, $filters);

        return $filters;
    }

    /**
     * Enable the sql filters.
     *
     * @param string[] $filters The filter names
     *
     * @return string[]
     *
     * @final
     */
    protected function enableSqlFilters(array $filters = []): array
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/doctrine-bundle".');
        }

        $em = $this->container->get('doctrine')->getManager();
        SqlFilterUtil::enableFilters($em, $filters);

        return $filters;
    }

    /**
     * Get the sql filter.
     *
     * @param string $name The filter name
     *
     * @final
     */
    protected function getSqlFilter(string $name): SQLFilter
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/doctrine-bundle".');
        }

        $em = $this->container->get('doctrine')->getManager();

        return $em->getFilters()->getFilter($name);
    }
}
