<?php

namespace NTI\LogBundle\Services;

use AppBundle\Entity\User\User;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Connection;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use NTI\LogBundle\Exception\SlackException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use NTI\LogBundle\Entity\Log;

class Logger {

    /** @var  Connection $connection */
    private $connection;
    private $container;
    private $serializer;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->serializer = SerializerBuilder::create()->build();
        $this->connection = $container->get('doctrine')->getConnection();
    }

    public function getUser()
    {

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            // no authentication information is available
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user->getUsername();
    }

    public function logNotice($message, $action = Log::ACTION_INFO, $entity = null) {
        $this->log($message, $action, $entity, Log::LEVEL_NOTICE);
    }

    public function logSuccess($message, $action = Log::ACTION_INFO, $entity = null) {
        $this->log($message, $action, $entity, Log::LEVEL_SUCCESS);
    }
    
    public function logWarning($message, $action = Log::ACTION_INFO, $entity = null) {
        $this->log($message, $action, $entity, Log::LEVEL_WARNING);
    }
    
    public function logDebug($message, $action = Log::ACTION_DEBUG, $entity = null) {
        $this->log($message, $action, $entity, Log::LEVEL_DEBUG);
    }    
    
    public function logError($message, $action = Log::ACTION_INFO, $entity = null) {
        $this->log($message, $action, $entity, Log::LEVEL_ERROR);
    }
    
    public function logException(\Exception $ex) {
        $this->log($ex->getMessage(), Log::ACTION_EXCEPTION, null, Log::LEVEL_ERROR, $ex);
    }

    public function logSlack($message, $level = Log::LEVEL_NOTICE, $entity = null) {
        if(!$this->container->has('nexy_slack.client')) {
            $this->log("Attempted to use Slack logging but the bundle is not properly configured.");
            return;
        }

        $channel = $this->container->getParameter('nti_log.nexy_slack.channel');
        $from = $this->container->getParameter('nti_log.nexy_slack.from');
        $icon = $this->container->getParameter('nti_log.nexy_slack.icon');

        $slack = $this->container->get('nexy_slack.client');

        $message = $slack->createMessage();

        $message
            ->to($channel)
            ->from($from)
            ->withIcon($icon)
            ->setText($message)
        ;

        try {
            $slack->sendMessage($message);
        } catch (\Exception $ex) {
            $this->logException(new SlackException($ex->getMessage(), $ex->getCode()));
        }

    }

    private function log($message, $action = Log::ACTION_INFO, $entity = null, $level = Log::LEVEL_NOTICE, \Exception $ex = null) {

        if(null !== $entity) {

            $excludes = $this->container->getParameter('nti_log.exclude');
            
            if(!is_array($excludes)) {
                $excludes = [];
            }

            if(in_array(get_class($entity), $excludes)){
                return;
            }

            $reader = new AnnotationReader();
            $excludeByAnnotation = $reader->getClassAnnotation(new \ReflectionClass($entity), 'NTI\\LogBundle\\Annotations\\ExcludeDoctrineLogging');
            if($excludeByAnnotation) {
                return;
            }

            $serializedEntity = $this->serializer->serialize($entity, 'json', SerializationContext::create()->setGroups(array('log')));

        } else {
            $serializedEntity = "";
            $entity = "";
        }

        $log = new Log();
        $log->setAction($action);
        $log->setLevel($level);
        if(is_object($entity)) {
            $log->setEntity(get_class($entity));
        }
        $log->setMessage($message);
        $log->setDate();
        $log->setSerializedEntity($serializedEntity);
        $log->setUser($this->getUser());

        if(null !== $ex) {
            $log->setExceptionCode($ex->getCode());
            $log->setExceptionFile($ex->getFile());
            $log->setExceptionLine($ex->getLine());
        }

        $sqlParameters = array(
          "level" => $log->getLevel(),
          "action" => $log->getAction(),
          "entity" => $log->getEntity(),
          "message" => $log->getMessage(),
          "date" => $log->getDate()->format('Y-m-d h:i:s'),
          "ipaddress" => $log->getIpaddress(),
          "user" => $log->getUser(),
          "exceptionCode" => $log->getExceptionCode(),
          "exceptionFile" => $log->getExceptionFile(),
          "exceptionLine" => $log->getExceptionLine(),
          "serializedEntity" => $log->getSerializedEntity()
        );

        $fields = implode(', ', array_keys($sqlParameters));
        $values = array_values($sqlParameters);
        array_walk($values, function(&$val) {
            $val = '"'.addslashes($val).'"';
        });
        $values = implode(', ', $values);

        $sql = "INSERT INTO nti_log({$fields}) VALUES({$values})";

        $stmt = $this->connection->prepare($sql);

        try{
            $stmt->execute();
        } catch(\Exception $ex) {
            error_log($ex->getMessage());
        }

        // Trigger slack logging
        $slackEnabled = $this->container->getParameter('nti_log.nexy_slack.enabled');
        $slackReplicate = $this->container->getParameter('nti_log.nexy_slack.replicate_logs');
        $slackReplicateLevels = $this->container->getParameter('nti_log.nexy_slack.replicate_levels');

        if($slackEnabled && $slackReplicate && in_array($level, $slackReplicateLevels) && ($ex instanceof SlackException == false)) {
            $this->logSlack($message, $level, $entity);
        }
    }
}