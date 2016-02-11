<?php

namespace Meare\Juggler\HttpClient;


use Meare\Juggler\Exception\Mountebank\MountebankException;

interface IHttpClient
{
    /**
     * @param string $host
     */
    public function setHost(string $host);

    /**
     * @param string $path
     * @return string Response body
     * @throws MountebankException
     */
    public function get(string $path) : string;

    /**
     * @param string $path
     * @param string $data
     * @return string Response body
     * @throws MountebankException
     */
    public function post(string $path, string $data) : string;

    /**
     * @param string $path
     * @param string $data
     * @return string Response body
     * @throws MountebankException
     */
    public function put(string $path, string $data) : string;

    /**
     * @param string $path
     * @return string Response body
     * @throws MountebankException
     */
    public function delete(string $path) : string;
}
