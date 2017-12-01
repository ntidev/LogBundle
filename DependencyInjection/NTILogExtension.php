<?php

namespace NTI\LogBundle\DependencyInjection;

use NTI\LogBundle\Entity\Log;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NTILogExtension extends Extension
{
    private $defaultConfiguration = array(
        'database' => array(
            'connection_name' => 'default',
        ),
        'exclude' => array(

        ),
        // Integration with NexySlackBundle
        // https://github.com/nexylan/slack-bundle
        'nexy_slack' => array(
            'enabled' => false,
            'replicate_logs' => false,
            'replicate_levels' => array(Log::LEVEL_ERROR, Log::LEVEL_DEBUG),
            'channel' => null,
            'from' => 'NTILogBundle',
            'icon' => ':ghost:',
        )
    );

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('config.yml');

        // Parse configuration
        if(isset($config['database']) && isset($config['database']['connection_name']))
            $this->defaultConfiguration['database']['connection_name'] = $config['database']['connection_name'];
        if(isset($config['exclude']))
            $this->defaultConfiguration['exclude'] = $config['exclude'];
        if(isset($config["nexy_slack"])) {
            $this->defaultConfiguration['nexy_slack'] = array_merge($this->defaultConfiguration['nexy_slack'], $config['nexy_slack']);
        }


        $container->setParameter( 'nti_log.database.connection_name', $this->defaultConfiguration['database']['connection_name']);
        $container->setParameter( 'nti_log.exclude', $this->defaultConfiguration['exclude']);
        $container->setParameter( 'nti_log.nexy_slack.enabled', $this->defaultConfiguration['nexy_slack']['enabled']);
        $container->setParameter( 'nti_log.nexy_slack.replicate_logs', $this->defaultConfiguration['nexy_slack']['replicate_logs']);
        $container->setParameter( 'nti_log.nexy_slack.replicate_levels', $this->defaultConfiguration['nexy_slack']['replicate_levels']);
        $container->setParameter( 'nti_log.nexy_slack.channel', $this->defaultConfiguration['nexy_slack']['channel']);
        $container->setParameter( 'nti_log.nexy_slack.from', $this->defaultConfiguration['nexy_slack']['from']);
        $container->setParameter( 'nti_log.nexy_slack.icon', $this->defaultConfiguration['nexy_slack']['icon']);

    }
}

