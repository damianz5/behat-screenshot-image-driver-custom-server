<?php

namespace Bex\Behat\ScreenshotExtension\Driver;

use Bex\Behat\ScreenshotExtension\Driver\ImageDriverInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bex\Behat\ScreenshotExtension\Driver\Service\CustomServerApi;

class CustomServer implements ImageDriverInterface
{
    const CONFIG_PARAM_AUTHKEY = 'authkey';
    const CONFIG_PARAM_COLLECTION_NAME = 'collection_name';
    const CONFIG_PARAM_REQUEST_URL = 'request_url';
    const CONFIG_PARAM_IMAGE_BASE_URL = 'image_base_url';
    const CONFIG_PARAM_CLIENT_FACTORY = 'client_factory';
    const GLOBAL_PARAMS_KEYS = [
        'BEHAT_SCREENSHOT_AUTHKEY',
        'BEHAT_SCREENSHOT_COLLECTION_NAME',
        'BEHAT_SCREENSHOT_REQUEST_URL',
        'BEHAT_SCREENSHOT_IMAGE_BASE_URL',
    ];

    /**
     * @var array
     */
    private $options = [
        self::CONFIG_PARAM_AUTHKEY          => null,
        self::CONFIG_PARAM_COLLECTION_NAME  => null,
        self::CONFIG_PARAM_REQUEST_URL      => null,
        self::CONFIG_PARAM_IMAGE_BASE_URL   => null,
    ];

    /**
     * @var CustomServerApi
     */
    private $api;

    /**
     * @param  ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode(self::CONFIG_PARAM_AUTHKEY)->defaultNull()->end()
            ->scalarNode(self::CONFIG_PARAM_COLLECTION_NAME)->defaultNull()->end()
            ->scalarNode(self::CONFIG_PARAM_REQUEST_URL)->defaultNull()->end()
            ->scalarNode(self::CONFIG_PARAM_IMAGE_BASE_URL)->defaultNull()->end()
            ->scalarNode(self::CONFIG_PARAM_CLIENT_FACTORY)->defaultFalse()->end()
            ->end();
    }

    /**
     * @param  ContainerBuilder $container
     * @param  array $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->options = array_merge($this->options, $config);

        $this->appendGlobalVariablesToOptions();
        $this->validateOptions();

        $clientFactory = $config[self::CONFIG_PARAM_CLIENT_FACTORY] ?: [$this, 'createCustomServerApi'];

        if (!is_callable($clientFactory)) {
            throw new \RuntimeException('Invalid API client factory callback');
        }

        $this->api = call_user_func($clientFactory);
        $this->api->setOptions($this->options);
    }

    /**
     * @param string $binaryImage
     * @param string $filename
     *
     * @return string URL to the image
     */
    public function upload($binaryImage, $filename)
    {
        return $this->api->call($binaryImage, $filename);
    }

    /**
     * Append Global variables to options
     */
    private function appendGlobalVariablesToOptions()
    {
        foreach (self::GLOBAL_PARAMS_KEYS as $paramName) {
            if (array_key_exists($paramName, $_SERVER)) {
                $keyName = strtolower(str_replace("BEHAT_SCREENSHOT_", null, $paramName));
                $this->options[ $keyName ] = $_SERVER[$paramName];
            }
        }
    }

    /**
     *
     * @return CustomServerApi
     */
    private function createCustomServerApi()
    {
        return new CustomServerApi();
    }

    private function validateOptions()
    {
        foreach ($this->options as $key => $option) {
            if (is_null($option)) {
                throw new \RuntimeException(sprintf('Missing Custom Server Driver configuration for key %s', $key));
            }
        }
    }
}
