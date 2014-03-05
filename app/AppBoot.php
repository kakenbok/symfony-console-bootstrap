<?php

use TestsAlwaysIncluded\SymfonyConsoleBootstrap\Boot\BootInterface;

class AppBoot implements BootInterface
{
    /**
     * Return the array of BundleInterface[] classes
     */
    public function getBundles()
    {
        return array();
    }
    
    /**
     * Return the configuration filename
     */
    public function getConfigFilename()
    {
        return 'config.yml';
    }

    /**
     * Return the Key/Value pair array
     */
    public function getParameters($applicationRoot)
    {
        return array(
            'boot.app_dir' => $applicationRoot,
        );
    }
}
