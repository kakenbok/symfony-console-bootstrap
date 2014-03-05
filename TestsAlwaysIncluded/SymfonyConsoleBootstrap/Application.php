<?php

namespace TestsAlwaysIncluded\SymfonyConsoleBootstrap;

use TestsAlwaysIncluded\SymfonyConsoleBootstrap\Boot\BootInterface;
use TestsAlwaysIncluded\SymfonyConsoleBootstrap\Boot\BootException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\FileLocator;

class Application extends BaseApplication
{
    protected $bootstrap;
    protected $container;

    /**
     * Get all boot information from the $bootstrap
     *
     * @param BootInterface $bootstrap
     */
    public function __construct(BootInterface $bootstrap)
    {
        $this->setBootstrap($bootstrap);
        parent::__construct('TestsAlwaysIncluded\\SymfonyConsoleBootstrap');
    }

    /**
     * $applicationRoot will be the root path
     *
     * @param string $applicationRoot
     */
    public function boot($applicationRoot)
    {
        $this->bootContainer($applicationRoot);
        $this->registerBundles();
        $this->loadConfiguration($applicationRoot);

        $this->getContainer()->compile();
        $this->registerBundleCommands();
    }

    /**
     * Collect the parameters from the Bootstrap and
     * Create the Container Builder
     *
     * @param string $applicationRoot
     */
    protected function bootContainer($applicationRoot)
    {
        $parameters = $this->getBootstrap()->getParameters($applicationRoot);
        $parameterBag = new ParameterBag($parameters);
        $container = new ContainerBuilder($parameterBag);
        $this->setContainer($container);
    }

    /**
     * Pass the container to Container Aware Command classes
     *
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
       if($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->getContainer());
       }
       return parent::doRunCommand($command, $input, $output);
    }

    /**
     * Load the configuration file into the container
     *
     * @param string $applicationRoot
     */
    protected function loadConfiguration($applicationRoot)
    {
        $filename = $this->getBootstrap()->getConfigFilename();
        $filePath = $applicationRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
        $fileLocator = new FileLocator($filePath);
        $fileLoader = null;

        $extension = substr($filename, strrpos($filename, '.')+1);
        switch(strtolower($extension))
        {
            case 'yml':
                $fileLoader = new YamlFileLoader($this->getContainer(), $fileLocator);
                break;
            default:
                throw new BootException('Unsupported config file extension: ' . $extension);
                break;
        }
        $fileLoader->load($filename);
    }

    /**
     * Register any bundle commands
     */
    protected function registerBundleCommands()
    {
        $bundles = $this->getBootstrap()->getBundles();
        foreach($bundles as $bundle)
        {
            if($bundle instanceof Bundle)
            {
                $bundle->registerCommands($this);
            }
        }
    }

    /**
     * Register any provided bundles
     */
    protected function registerBundles()
    {
        $container = $this->getContainer();
        $bundles = $this->getBootstrap()->getBundles();
        foreach($bundles as $bundle)
        {
            if(! $bundle instanceof BundleInterface)
            {
                continue;
            }
            $containerExtension = $bundle->getContainerExtension();
            if(false === is_null($containerExtension))
            {
                $container->registerExtension($containerExtension);
            }
            $bundle->build($container);
        }
    }

    /**
     * Sets the BootInterface instance
     *
     * @param BootInterface $bootstrap
     */
    protected function setBootstrap(BootInterface $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * Returns the BootInterface bootstrap
     *
     * @return BootInterface
     */
    protected function getBootstrap()
    {
        return $this->bootstrap;
    }

    /**
     * Setter method for the Container
     *
     * @param ContainerBuilder $symfonyContainer
     */
    protected function setContainer(ContainerBuilder $symfonyContainer)
    {
        $this->container = $symfonyContainer;
    }

    /**
     * Returns the Container
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
