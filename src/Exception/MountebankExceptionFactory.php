<?php

namespace Meare\Juggler\Exception;


use Meare\Juggler\Exception\Mountebank\MountebankException;

class MountebankExceptionFactory
{
    const ERROR_BAD_DATA = 'bad data';
    const ERROR_INVALID_INJECTION = 'invalid injection';
    const ERROR_RESOURCE_CONFLICT = 'resource conflict';
    const ERROR_INSUFFICIENT_ACCESS = 'insufficient access';
    const ERROR_INVALID_PROXY = 'invalid proxy';
    const ERROR_NO_SUCH_RESOURCE = 'no such resource';
    const ERROR_INVALID_JSON = 'invalid JSON';

    /**
     * @var array
     */
    private $errorMap = [
        self::ERROR_BAD_DATA            => Mountebank\BadDataException::class,
        self::ERROR_INVALID_INJECTION   => Mountebank\InvalidInjectionException::class,
        self::ERROR_RESOURCE_CONFLICT   => Mountebank\ResourceConflictException::class,
        self::ERROR_INSUFFICIENT_ACCESS => Mountebank\InsufficientAccessException::class,
        self::ERROR_INVALID_PROXY       => Mountebank\InvalidProxyException::class,
        self::ERROR_NO_SUCH_RESOURCE    => Mountebank\NoSuchResourceException::class,
        self::ERROR_INVALID_JSON        => Mountebank\InvalidJsonException::class,
    ];

    /**
     * Takes first error from mountebank response and wraps it into according MountebankException
     *
     * @param string $response_body
     * @return MountebankException
     */
    public function createInstanceFromMountebankResponse(string $response_body) : MountebankException
    {
        $errors = \GuzzleHttp\json_decode($response_body, true)['errors'];
        $first_error = reset($errors);

        return $this->createInstance(
            $first_error['code'],
            $first_error['message'],
            $first_error['source'] ?? null,
            $first_error['data'] ?? null
        );
    }

    /**
     * @param string $error_code
     * @param string $message
     * @param string $source
     * @param string $data
     * @return MountebankException
     */
    public function createInstance(string $error_code, string $message, string $source = null, string $data = null) : MountebankException
    {
        $exception_class = $this->errorMap[$error_code] ?? null;
        if (null === $exception_class) {
            throw new \InvalidArgumentException('Unable to instantiate MountebankException; ' .
                "no class found for mountebank error code '$error_code'");
        }

        return new $exception_class($message, $source, $data);
    }
}
