<?php

namespace Meare\Juggler\Imposter\Stub\Response;


class IsResponse implements IResponse
{
    const MODE_TEXT = 'text';
    const MODE_BINARY = 'binary';

    /**
     * @var string
     */
    private $type = self::TYPE_IS;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * Starting from Juggler 1.4.3 inline JSON is allowed as http/s response bodies
     *
     * @var string|array
     */
    private $body;

    /**
     * @var string
     */
    private $mode = self::MODE_TEXT;

    /**
     * @param int          $status_code
     * @param array        $headers
     * @param string|array $body
     * @param string       $mode
     */
    public function __construct($status_code = 200, array $headers = [], $body = null, $mode = null)
    {
        $this->setStatusCode($status_code);
        $this->setHeaders($headers);
        if (null !== $body) {
            $this->setBody($body);
        }
        if (null !== $mode) {
            $this->setMode($mode);
        }
    }

    /**
     * @param array $contract
     * @return self
     */
    public static function createFromContract($contract)
    {
        if (!isset($contract['statusCode'])) {
            throw new \InvalidArgumentException("Cannot create IsResponse from contract: Invalid contract; 'statusCode' field does not exists");
        }

        return new self(
            $contract['statusCode'],
            isset($contract['headers']) ? $contract['headers'] : [],
            isset($contract['body']) ? $contract['body'] : null,
            isset($contract['_mode']) ? $contract['_mode'] : null
        );
    }

    /**
     * @param callable $callback
     */
    public function modifyBody(callable $callback)
    {
        $body = $callback($this->getBody());
        if (null === $body) {
            throw new \LogicException('Expected new body as return value, got null');
        }
        $this->setBody($body);
    }

    /**
     * @return array|string mountebank 1.4.3: You can now use inline JSON for http/s response bodies rather than a
     *                      string.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param array|string $body
     */
    public function setBody($body)
    {
        if (!is_string($body) && !is_array($body)) {
            throw new \InvalidArgumentException('Unable to set IsResponse body; Expected body to be string or array, '
                . 'got ' . gettype($body));
        }
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            IResponse::TYPE_IS => [
                'statusCode' => $this->getStatusCode(),
                'headers'    => $this->getHeaders(),
                'body'       => $this->getBody(),
                '_mode'      => $this->getMode(),
            ],
        ];
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $status_code
     */
    public function setStatusCode($status_code)
    {
        $this->statusCode = $status_code;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * TODO headers with same name
     *
     * @param array $headers ['name' => 'value', ..]
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        if (!in_array($mode, [self::MODE_TEXT, self::MODE_BINARY])) {
            throw new \InvalidArgumentException("Unable to set IsResponse mode; Invalid mode: '$mode'");
        }
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                'Unable to set IsResponse headers; Header name expected be string, '
                . gettype($name) . " received ('" . (string)$name . "')"
            );
        }
        $this->headers[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }
}
