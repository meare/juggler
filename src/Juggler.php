<?php

namespace Meare\Juggler;


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
    public function __construct(string $host, int $port = self::DEFAULT_PORT, IHttpClient $httpClient = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->setUrl($host, $port);
        $this->httpClient = $httpClient ?? GuzzleClient::create();
        $this->httpClient->setHost($this->getUrl());
        $this->abstractImposterBuilder = new AbstractImposterBuilder;
    }

    /**
     * @param string $host
     * @param int    $port
     */
    private function setUrl(string $host, int $port)
    {
        $this->url = 'http://' . $host . ':' . $port;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort() : int
    {
        return $this->port;
    }

    /**
     * @param string $path
     * @throws \InvalidArgumentException in case contract contents are not valid JSON
     * @throws \RuntimeException if save to filesystem failed
     * @return Imposter
     */
    public function createImposterFromFile(string $path) : Imposter
    {
        return $this->abstractImposterBuilder->build(file_get_contents($path));
    }

    /**
     * @param string $path
     * @throws MountebankException
     * @throws \RuntimeException if file does not exist
     * @return int Imposter port
     */
    public function postImposterFromFile(string $path)
    {
        return $this->postImposterContract(file_get_contents($path));
    }

    /**
     * @param string $contract
     * @throws MountebankException
     * @return int Imposter port
     */
    public function postImposterContract(string $contract)
    {
        $received_contract = $this->httpClient->post('/imposters', $contract);

        return (int)\GuzzleHttp\json_decode($received_contract, true)['port'];
    }

    /**
     * @param int  $port
     * @param bool $replayable
     * @param bool $remove_proxies
     * @return HttpImposter
     */
    public function getHttpImposter(int $port, bool $replayable = false, bool $remove_proxies = false) : HttpImposter
    {
        return $this->getImposter($port, $replayable, $remove_proxies);
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
    public function getImposter(int $port, bool $replayable = false, bool $remove_proxies = false) : Imposter
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
    public function getImposterContract(int $port, bool $replayable = false, bool $remove_proxies = false) : string
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
    public function deleteImposter($imposter, bool $replayable = false, bool $remove_proxies = false)
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
    public function deleteImposterIfExists($imposter, bool $replayable = false, bool $remove_proxies = false)
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
    public function postImposter(Imposter $imposter) : int
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
    public function removeProxies(int $port)
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
    public function retrieveAndSaveContract($port, string $path)
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
    public function saveContract(Imposter $imposter, string $path)
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
    private function composeQueryString(bool $replayable, bool $remove_proxies) : string
    {
        return http_build_query(array_filter([
            self::PARAM_REPLAYABLE     => $replayable ? 'true' : null,
            self::PARAM_REMOVE_PROXIES => $remove_proxies ? 'true' : null,
        ]));
    }
}
