<?php

namespace Meare\Juggler\Exception\Mountebank;


/**
 * Wrapper mountebank errors, more info:
 * http://www.mbtest.org/docs/api/errors
 */
class MountebankException extends \Exception
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $data;

    /**
     * MountebankException constructor.
     *
     * @param string          $message
     * @param string|null     $source
     * @param string|null     $data
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $source = null, $data = null, $code = 0, \Exception $previous = null)
    {
        $this->source = $source;
        $this->data = $data;

        $exception_message = $message
            . (isset($source) ? "; source: '$source'" : '')
            . (isset($data) ? " - $data" : '');

        parent::__construct($exception_message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
