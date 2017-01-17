<?php

namespace Meare\Juggler;


use Meare\Juggler\Exception\Client\ClientException;
use Meare\Juggler\Exception\Mountebank\MountebankException;
use Meare\Juggler\Exception\Mountebank\NoSuchResourceException;
use Meare\Juggler\HttpClient\GuzzleClient;
use Meare\Juggler\HttpClient\IHttpClient;
use Meare\Juggler\Imposter\Builder\AbstractImposterBuilder;
use Meare\Juggler\Imposter\HttpImposter;
use Meare\Juggler\Imposter\Imposter;

class Juggler
{
    const PARAM_REPLAYABLE = 'replayable';
    const PARAM_REMOVE_PROXIES = 'remove_proxies';
    const DEFAULT_PORT = 2525;

    /**
     * @var IHttpClient
     */
    private $httpClient;

    /**
     * @var AbstractImposterBuilder
     */
    private $abstractImposterBuilder;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * Full URL to Juggler client
     *
     * @var string
     */
    private $url;

    /**
     * @param string      $host
     * @param int         $port
     * @param IHttpClient $httpClient
     */
    public function __construct($host, $port = self::DEFAULT_PORT, IHttpClient $httpClient = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->setUrl($host, $port);
        $this->httpClient = isset($httpClient) ? $httpClient : GuzzleClient::create();
        $this->httpClient->setHost($this->getUrl());
        $this->abstractImposterBuilder = new AbstractImposterBuilder;
    }

    /**
     * @param string $host
     * @param int    $port
     */
    private function setUrl($host, $port)
    {
        $this->url = 'http://' . $host . ':' . $port;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $path
     * @throws \InvalidArgumentException in case contract contents are not valid JSON
     * @throws \RuntimeException if save to filesystem failed
     * @return Imposter
     */
    public function createImposterFromFile($path)
    {
        return $this->abstractImposterBuilder->build(file_get_contents($path));
    }

    /**
     * @param string $path
     * @throws MountebankException
     * @throws \RuntimeException if file does not exist
     * @return int Imposter port
     */
    public function postImposterFromFile($path)
    {
        return $this->postImposterContract(file_get_contents($path));
    }

    /**
     * @param string $contract
     * @throws MountebankException
     * @return int Imposter port
     */
    public function postImposterContract($contract)
    {
        $received_contract = $this->httpClient->post('/imposters', $contract);

        return (int)\GuzzleHttp\json_decode($received_contract, true)['port'];
    }

    /**
     * @param int  $port
     * @param bool $replayable
     * @param bool $remove_proxies
     * @return HttpImposter
     * @throws ClientException in case imposter mountebank returns is not http imposter
     */
    public function getHttpImposter($port, $replayable = false, $remove_proxies = false)
    {
        $imposter = $this->getImposter($port, $replayable, $remove_proxies);

        if (!$imposter instanceof HttpImposter) {
            throw new ClientException(
                "Expected imposter on port $port to be http imposter; got {$imposter->getProtocol()} imposter"
            );
        }

        return $imposter;
    }

    /**
     * Retrieves contract and builds Imposter
     *
     * @param int  $port
     * @param bool $replayable
     * @param bool $remove_proxies
     * @throws MountebankException
     * @return Imposter
     */
    public function getImposter($port, $replayable = false, $remove_proxies = false)
    {
        return $this->abstractImposterBuilder->build($this->getImposterContract($port, $replayable, $remove_proxies));
    }

    /**
     * @param int  $port
     * @param bool $replayable
     * @param bool $remove_proxies
     * @throws MountebankException
     * @return string
     */
    public function getImposterContract($port, $replayable = false, $remove_proxies = false)
    {
        $query = $this->composeQueryString($replayable, $remove_proxies);

        return $this->httpClient->get("/imposters/$port?$query");
    }

    /**
     * @param Imposter $imposter
     * @return int Imposter port
     */
    public function replaceImposter(Imposter $imposter)
    {
        $this->deleteImposter($imposter);
        return $this->postImposter($imposter);
    }

    /**
     * @param int|Imposter $imposter Port or Imposter instance
     * @param bool         $replayable
     * @param bool         $remove_proxies
     *
     * @return string Imposter contract
     * @throws MountebankException
     */
    public function deleteImposter($imposter, $replayable = false, $remove_proxies = false)
    {
        $query = $this->composeQueryString($replayable, $remove_proxies);
        $port = $imposter instanceof Imposter ? $imposter->getPort() : $imposter;

        return $this->httpClient->delete("/imposters/$port?$query");
    }

    /**
     * @param int|Imposter $imposter
     * @param bool         $replayable
     * @param bool         $remove_proxies
     * @return string|null Imposter contract or null if there was no requested imposter
     */
    public function deleteImposterIfExists($imposter, $replayable = false, $remove_proxies = false)
    {
        try {
            return $this->deleteImposter($imposter, $replayable, $remove_proxies);
        } catch (NoSuchResourceException $e) {
            return null;
        }
    }

    /**
     * @param Imposter $imposter
     * @throws MountebankException
     * @return int Imposter port
     */
    public function postImposter(Imposter $imposter)
    {
        $port = $this->postImposterContract(json_encode($imposter));
        if (!$imposter->hasPort()) {
            $imposter->setPort($port);
        }

        return $port;
    }

    /**
     * @throws MountebankException
     */
    public function deleteImposters()
    {
        $this->httpClient->delete("/imposters");
    }

    /**
     * @param int $port
     * @throws MountebankException
     */
    public function removeProxies($port)
    {
        $query = $this->composeQueryString(false, true);
        $this->httpClient->get("/imposters/$port?$query");
    }

    /**
     * Retrieves imposter contract and saves it to a local filesystem
     *
     * @param int    $port
     * @param string $path
     * @throws \RuntimeException if save to filesystem failed
     */
    public function retrieveAndSaveContract($port, $path)
    {
        file_put_contents($path, $this->getImposterContract($port));
    }

    /**
     * Saves Imposter contract to local filesystem
     *
     * @param Imposter $imposter
     * @param string   $path
     * @throws \RuntimeException if save to filesystem failed
     */
    public function saveContract(Imposter $imposter, $path)
    {
        file_put_contents($path, \GuzzleHttp\json_encode($imposter));
    }

    /**
     * mountebank API only supports string 'true' as boolean param value
     *
     * @param bool $replayable
     * @param bool $remove_proxies
     *
     * @return string
     */
    private function composeQueryString($replayable, $remove_proxies)
    {
        return http_build_query(array_filter([
            self::PARAM_REPLAYABLE     => $replayable ? 'true' : null,
            self::PARAM_REMOVE_PROXIES => $remove_proxies ? 'true' : null,
        ]));
    }
}
