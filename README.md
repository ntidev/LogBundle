# NTISyncBundle


### Installation

1. Install the bundle using composer:

```
$ composer require ntidev/log-bundle "dev-master"
```


2. Add the bundle configuration to the AppKernel


```
public function registerBundles()
{
    $bundles = array(
        ...
        new NTI\LogBundle\NTILogBundle(),
        ...
    );
}
```

3. Setup the configuration in the ``config.yml``

```
# NTI
nti_log:
    exclude: [ 'JMose\CommandSchedulerBundle\Entity\ScheduledCommand' ]     # default: []
```

The ``exclude`` allows you to exclude logging for specific entities that change at a rapid rate (for example the User entity usually registers changes when users log in)

4. Update the database schema

```
$ php app/console doctrine:schema:update
```


### Usage

1. Get the Logging service

```
$logger = $container->get('nti.logger');
```

The following methods are available for logging:

```
logNotice($message, $action = Log::ACTION_INFO, $entity = null)
logSuccess($message, $action = Log::ACTION_INFO, $entity = null)
logWarning($message, $action = Log::ACTION_INFO, $entity = null) 
logDebug($message, $action = Log::ACTION_DEBUG, $entity = null)
logError($message, $action = Log::ACTION_INFO, $entity = null)    
logException(\Exception $ex) 
logSlack($message, $level = Log::LEVEL_NOTICE, $entity = null) 
```

Example:

```
$service->logDebug("Hello World")
```


### Event Listeners

The bundle comes with 2 event subscribers: ``DoctrineEventSubscriber`` and ``KernelExceptionListerner``.

The ``DoctrineEventSubscriber`` will listener for the following events:

* PostPersist
* PostUpdate
* PostRemove

And it will log the changes automatically into the database.

The ``KernelExceptionListener`` will capture all exceptions and log them into the database as well. However, if you capture an exception you must manually log it with the service, for example:

```
try {
    ...
    $em->flush()
} catch(\Exception $ex) {
    $this->get('nti.logger')->logException($ex);
    ...
}
```

### Slack Integration

If the [NexySlackBundle](https://github.com/nexylan/slack-bundle "https://github.com/nexylan/slack-bundle") is used, you can integrate this bundle to throw the information to a channel in Slack as well.

The configuration piece as shown above serves to configure how the NTILogBundle should post to Slack:

```
# NTI
nti_log:
    ...    
    # In case NexySlackBundle is used
    nexy_slack:
        enabled:    # default: false
        replicate_logs: true     # default: false
        replicate_levels: [ERROR, DEBUG]   # default: [ERROR]
        channel: "#alertchannel"  # default: empty, required
        
# NexySlackBundle
nexy_slack:
    # The Slack API Incoming WebHooks URL.
    endpoint: "[see https://api.slack.com/tokens to generate the webhook for this app]"
```