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

use Doctrine\Common\Persistence\ManagerRegistry;
use Klipper\Bundle\ApiBundle\View\ViewHandlerInterface;
use Klipper\Component\Content\Downloader\DownloaderInterface;
use Klipper\Component\Content\Uploader\UploaderInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Resource\Converter\ConverterRegistryInterface;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Resource\Handler\FormHandlerInterface;
use Klipper\Component\Security\ObjectFilter\ObjectFilterInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\SecurityExtra\Authentication\AuthenticationHelper;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Abstract controller for API.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AbstractController implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    use ControllerTrait;

    public static function getSubscribedServices(): array
    {
        return [
            'klipper_api.controller_handler' => '?'.ControllerHandler::class,
            'klipper_api.view_handler' => '?'.ViewHandlerInterface::class,
            'klipper_api.expression_language.repository' => '?'.ExpressionLanguage::class,
            'klipper_metadata.manager' => '?'.MetadataManagerInterface::class,
            'klipper_content.downloader' => '?'.DownloaderInterface::class,
            'klipper_content.uploader' => '?'.UploaderInterface::class,
            'klipper_security_extra.authentication_helper' => '?'.AuthenticationHelper::class,
            'doctrine' => '?'.ManagerRegistry::class,
            'klipper_resource.converter_registry' => '?'.ConverterRegistryInterface::class,
            'klipper_resource.domain_manager' => '?'.DomainManagerInterface::class,
            'klipper_resource.form_handler' => '?'.FormHandlerInterface::class,
            'klipper_security.object_filter' => '?'.ObjectFilterInterface::class,
            'klipper_security.organizational_context' => '?'.OrganizationalContextInterface::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'http_kernel' => '?'.HttpKernelInterface::class,
            'message_bus' => '?'.MessageBusInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
            'request_stack' => '?'.RequestStack::class,
            'router' => '?'.RouterInterface::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'translator' => '?'.TranslatorInterface::class,
        ];
    }

    /**
     * Gets a container parameter by its name.
     *
     * @param string $name The parameter name
     *
     * @return mixed
     *
     * @final
     */
    protected function getParameter(string $name)
    {
        return $this->getService('parameter_bag')->get($name);
    }

    /**
     * Gets the converter registry.
     *
     * @final
     */
    protected function getConverterRegistry(): ConverterRegistryInterface
    {
        return $this->getService('klipper_resource.converter_registry');
    }

    /**
     * Gets the form handler.
     *
     * @final
     */
    protected function getFormHandler(): FormHandlerInterface
    {
        return $this->getService('klipper_resource.form_handler');
    }

    /**
     * Gets the domain manager.
     *
     * @final
     */
    protected function getDomainManager(): DomainManagerInterface
    {
        return $this->getService('klipper_resource.domain_manager');
    }

    /**
     * Gets the organizational context.
     *
     * @final
     */
    protected function getOrganizationalContext(): OrganizationalContextInterface
    {
        return $this->getService('klipper_security.organizational_context');
    }

    /**
     * Gets the object filter.
     *
     * @final
     */
    protected function getObjectFilter(): ObjectFilterInterface
    {
        return $this->getService('klipper_security.object_filter');
    }

    /**
     * Get the authorization checker.
     *
     * @final
     */
    protected function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->getService('security.authorization_checker');
    }

    /**
     * Check if the authentication is basic.
     *
     * @final
     */
    protected function isBasicAuthentication(): bool
    {
        return $this->getService('klipper_security_extra.authentication_helper')->isBasicAuthentication();
    }

    /**
     * Gets the controller handler.
     *
     * @final
     */
    protected function getControllerHandler(): ControllerHandler
    {
        return $this->getService('klipper_api.controller_handler');
    }

    /**
     * Gets the view handler.
     *
     * @final
     */
    protected function getViewHandler(): ViewHandlerInterface
    {
        return $this->getService('klipper_api.view_handler');
    }

    /**
     * Gets the view handler.
     *
     * @final
     */
    protected function getMetadataManager(): MetadataManagerInterface
    {
        return $this->getService('klipper_metadata.manager');
    }

    /**
     * Gets the downloader.
     *
     * @final
     */
    protected function getDownloader(): DownloaderInterface
    {
        return $this->getService('klipper_content.downloader');
    }

    /**
     * Get the translator.
     */
    protected function getTranslator(): TranslatorInterface
    {
        return $this->getService('translator');
    }

    /**
     * Get the doctrine repository expression language.
     */
    protected function getRepositoryExpressionLanguage(): ExpressionLanguage
    {
        return $this->getService('klipper_api.expression_language.repository');
    }

    /**
     * Get the service.
     *
     * @param string $name The service name
     *
     * @return mixed
     */
    private function getService(string $name)
    {
        if (!$this->container->has($name)) {
            throw new ServiceNotFoundException($name, null, null, [], sprintf('The "%s::%s()" method is missing a service to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "'.$name.'" in the service locator passed to the controller.', \get_class($this), __FUNCTION__));
        }

        return $this->container->get($name);
    }
}
