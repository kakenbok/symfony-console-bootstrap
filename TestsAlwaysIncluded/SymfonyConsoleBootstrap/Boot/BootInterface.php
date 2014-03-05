<?php

namespace TestsAlwaysIncluded\SymfonyConsoleBootstrap\Boot;

interface BootInterface
{
    /**
     * Returns a BundleInterface[] for registration
     *
     * @return BundleInterface[]
     */
    public function getBundles();

    /**
     * Returns the file name of the configuration file
     * for the container
     *
     * @return string
     */
    public function getConfigFilename();

    /**
     * Return an array of key/value pairs for the Container
     * ParameterBag
     *
     * @return string[]
     */
    public function getParameters($applicationRoot);
}
