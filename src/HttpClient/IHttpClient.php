<?php

namespace Meare\Juggler\HttpClient;


use Meare\Juggler\Exception\Mountebank\MountebankException;

interface IHttpClient
{
    /**
     * @param string $host
     */
    public function setHost($host);

    /**
     * @param string $path
     * @return string Response body
     * @throws MountebankException
     */
    public function get($path);

    /**
     * @param string $path
     * @param string $data
     * @return string Response body
     * @throws MountebankException
     */
    public function post($path, $data);

    /**
     * @param string $path
     * @param string $data
     * @return string Response body
     * @throws MountebankException
     */
    public function put($path, $data);

    /**
     * @param string $path
     * @return string Response body
     * @throws MountebankException
     */
    public function delete($path);
}
