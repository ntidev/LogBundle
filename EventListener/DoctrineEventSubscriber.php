<?php

namespace NTI\LogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use NTI\LogBundle\Entity\Log;
use NTI\LogBundle\Services\Logger;

class DoctrineEventListener
{
    private $container;
    /** @var Logger $logger */
    private $logger;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'preRemove',
        );
    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->logger = new Logger($this->container);
        $entity = $args->getObject();
        if(is_string($entity)) {
            $entity = null;
        }
        $this->logger->logSuccess("Created ".get_class($entity).".", Log::ACTION_CREATE, $entity);
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->logger = new Logger($this->container);
        $entity = $args->getObject();
        if(is_string($entity)) {
            $entity = null;
        }
        $this->logger->logSuccess("Updated ".get_class($entity).".", Log::ACTION_UPDATE, $entity);
    }    
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->logger = new Logger($this->container);
        $entity = $args->getObject();
        if(is_string($entity)) {
            $entity = null;
        }
        $this->logger->logWarning("Removed ".get_class($entity).".", Log::ACTION_DELETE, $entity);
    }  
}