<?php

namespace NTI\LogBundle\DependencyInjection;

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
        'exceptions' => array(
            'errors_only' => '',
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

        $aAsseticBundle = $container->getParameter('assetic.bundles');
        $aAsseticBundle[] = 'NTILogBundle';
        $container->setParameter('assetic.bundles', $aAsseticBundle);

        // Parse configuration
        if(isset($config['database']) && isset($config['database']['connection_name']))
            $this->defaultConfiguration['database']['connection_name'] = $config['database']['connection_name'];
        if(isset($config['exceptions']) && isset($config['exceptions']['errors_only']))
            $this->defaultConfiguration['exceptions']['errors_only'] = $config['exceptions']['errors_only'];

        $container->setParameter( 'nti_log.database.connection_name', $this->defaultConfiguration['database']['connection_name']);
        $container->setParameter( 'nti_log.exceptions.errors_only', $this->defaultConfiguration['exceptions']['errors_only']);



    }
}

