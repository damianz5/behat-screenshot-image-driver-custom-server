<?php
namespace Bex\Behat\ScreenshotExtension\Driver\Service;

use Buzz\Client\Curl;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomServerApi
{
    /**
     * @var array
     */
    private $options = array();

    /**
     * @var Curl
     */
    private $client;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Curl $client
     * @param Filesystem $filesystem
     */
    public function __construct(Curl $client = null, Filesystem $filesystem = null)
    {
        $this->client = $client ?: new Curl();
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * Set the options of the custom server passed in array.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(array(
            'authkey',
            'collection_name',
            'request_url',
            'image_base_url',
        ));
        $resolver->setDefined(array(
            'client_factory'
        ));

        $this->options = $resolver->resolve($options);
    }

    /**
     * @param  string $binaryImage
     * @param  string $filename
     *
     * @return string
     */
    public function call($binaryImage, $filename)
    {
        $response = new Response();
        $tmpFile = $this->filesystem->tempnam('screenshot', $filename);
        $this->filesystem->dumpFile($tmpFile, $binaryImage);

        $image = new FormUpload();
        $image->setFilename($filename);
        $image->loadContent($tmpFile);

        $request = $this->buildRequest($image);

        $this->client->setOption(CURLOPT_TIMEOUT, 10000);
        $this->client->setOption(CURLOPT_HTTPHEADER, array("AUTHKEY: " . $this->options['authkey']));
        $this->client->setOption(CURLOPT_BINARYTRANSFER, true);

        $this->client->send($request, $response);

        return $this->processResponse($response);
    }

    /**
     * @param  Response $response
     *
     * @return string
     */
    private function processResponse(Response $response)
    {
        $responseData = json_decode($response->getContent(), true);
        if (!isset($responseData['files']) || empty($responseData['files'])) {
            throw new \RuntimeException('Screenshot upload failed');
        }
        return $this->options['image_base_url'] . $responseData['files'][0];
    }

    /**
     * @param FormUpload $image
     *
     * @return FormRequest
     */
    private function buildRequest($image)
    {
        $request = new FormRequest();
        $request->fromUrl($this->options['request_url'] . $this->options['collection_name']);
        $request->setField('file', $image);
        return $request;
    }
}
