<?php

namespace Meare\Juggler\HttpClient;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Meare\Juggler\Exception\Mountebank\MountebankException;
use Meare\Juggler\Exception\MountebankExceptionFactory;

class GuzzleClient implements IHttpClient
{
    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var MountebankExceptionFactory
     */
    private $exceptionFactory;

    /**
     * @var string
     */
    private $host;

    /**
     * @param Client                     $client
     * @param MountebankExceptionFactory $exceptionFactory
     */
    public function __construct(Client $client, MountebankExceptionFactory $exceptionFactory)
    {
        $this->guzzleClient = $client;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * @return GuzzleClient
     */
    public static function create() : self
    {
        return new self(
            new Client, new MountebankExceptionFactory
        );
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = rtrim($host, '/');
    }

    /**
     * @param string $path
     * @return string
     * @throws MountebankException
     */
    public function get(string $path) : string
    {
        return $this->request('GET', $path);
    }

    /**
     * @param string      $method
     * @param string      $path
     * @param string|null $data
     * @return string
     * @throws MountebankException
     */
    public function request(string $method, string $path, string $data = null) : string
    {
        if (!$this->hasHost()) {
            throw new \LogicException('Host not set; Unable to perform request');
        }
        $options = [];
        if (null !== $data) {
            $options['body'] = $data;
        }
        try {
            return $this->guzzleClient
                ->request($method, $this->getUrl($path), $options)
                ->getBody();
        } catch (ClientException $e) {
            throw $this->convertToMountebankException($e);
        }
    }

    /**
     * @return bool
     */
    public function hasHost() : bool
    {
        return null !== $this->host;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getUrl(string $path) : string
    {
        return $this->host . $path;
    }

    /**
     * Creates MountebankException instance from mountebank response
     *
     * @param ClientException $e
     * @return MountebankException
     */
    private function convertToMountebankException(ClientException $e) : MountebankException
    {
        return $this->exceptionFactory->createInstanceFromMountebankResponse(
            (string)$e->getResponse()->getBody()
        );
    }

    /**
     * @param string $path
     * @param string $data
     * @return string
     * @throws MountebankException
     */
    public function post(string $path, string $data) : string
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * @param string $path
     * @param string $data
     * @return string
     * @throws MountebankException
     */
    public function put(string $path, string $data) : string
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * @param string $path
     * @return string
     * @throws MountebankException
     */
    public function delete(string $path) : string
    {
        return $this->request('DELETE', $path);
    }
}