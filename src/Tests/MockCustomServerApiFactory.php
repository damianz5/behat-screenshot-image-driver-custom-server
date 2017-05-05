<?php

namespace Bex\Behat\ScreenshotExtension\Driver\Tests;


/**
 * Class MockCustomServerApiFactory
 */
class MockCustomServerApiFactory
{
    private $options = [];

    /**
     * @return MockCustomServerApiFactory
     */
    public function getCustomServerApi()
    {
        return new self;
    }

    /**
     * Set the options of the custom server passed in array.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function call($binaryImage, $filename)
    {
        return sprintf(
            '%s/data/container-%s/%s',
            $this->options['image_base_url'],
            $this->options['collection_name'],
            $filename
        );
    }
}
