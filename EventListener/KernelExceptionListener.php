<?php

// src/AppBundle/EventListener/AcmeExceptionListener.php
namespace NTI\LogBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class KernelExceptionListener
{
    
    /**
     *
     * @var ContainerInterface
     */
    private $container;

    function __construct($container) {
        $this->container = $container;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event;
        $this->container->get('nti.logger')->logException($exception);
    }
}
