<?php

/**
 * This file is part of the Wall Poster bundle.
 *
 * (c) Ilya Pokamestov
 *
 * @author Ilya Pokamestov
 * @email dario_swain@yahoo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WallPosterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WallPosterExtension extends Extension
{
	protected $availableServices = array(
		'vk','facebook','twitter'
	);

    protected $configDirectory = '/../Resources/config';
    protected $configFiles = array(
        'services',
    );

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $this->configure($config, new Configuration(), $container);
    }

    /**
     * @param array                  $config
     * @param ConfigurationInterface $configuration
     * @param ContainerBuilder       $container
     *
     * @return array
     */
    public function configure(array $config, ConfigurationInterface $configuration, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration($configuration, $config);

        $loader = new XmlFileLoader($container, new FileLocator($this->getConfigurationDirectory()));

		foreach($this->availableServices as $service)
		{
			if(isset($config[$service]))
			{
				$this->mapParameters($service, $config[$service], $container);
				$this->loadConfigurationFile($service,$loader);
			}
		}

//		if(!$container->hasParameter('wall_poster.facebook.access_token'))
//		{
//			$container->setParameter('wall_poster.facebook.access_token',false);
//		}

        return array($config, $loader);
    }

    /**
     * Get the configuration directory
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getConfigurationDirectory()
    {
        $reflector = new \ReflectionClass($this);
        $fileName = $reflector->getFileName();

        if (!is_dir($directory = dirname($fileName) . $this->configDirectory)) {
            throw new \RuntimeException(sprintf('The configuration directory "%s" does not exists.', $directory));
        }

        return $directory;
    }

    /**
     * @param string         $filename
     * @param XmlFileLoader $loader
     */
    protected function loadConfigurationFile($filename, XmlFileLoader $loader)
    {
        if (file_exists($file = sprintf('%s/%s.xml', $this->getConfigurationDirectory(), $filename))) {
            $loader->load($file);
        }
    }

    /**
     * Remap parameters.
     *
     * @param string           $node
     * @param array            $parameters
     * @param ContainerBuilder $container
     */
    protected function mapParameters($node, array $parameters, ContainerBuilder $container)
    {
        foreach ($parameters as $key => $value) {
            $container->setParameter(sprintf('wall_poster.%s.%s',$node,$key), $value);
        }
    }
}
