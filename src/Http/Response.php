<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Http;

class Response
{
    protected string $_response;
    protected int $_code;
    protected mixed $_data;

    private bool $_processed = false;

    /**
     * @param string $response
     * @param int $code
     * @param bool $jsonException
     * @throws JsonException
     */
    public function __construct(string $response, int $code, bool $jsonException = true)
    {
        $response = utf8_decode($response);
        $response = ltrim($response, "?");
        $response = mb_convert_encoding($response, 'ISO-8859-1', 'UTF-8');

        $this->_response = $response;

        $this->_code = $code;

        $this->_processData($jsonException);
    }

    /**
     * @param bool $jsonException
     * @return mixed
     * @throws JsonException
     */
    protected function _processData(bool $jsonException = true): mixed
    {
        if (!$this->_processed) {
            $this->_data = json_decode($this->_response, true);

            if (json_last_error() && $jsonException) {
                throw new JsonException('json_decode: '. json_last_error_msg());
            }
        }

        return $this->_data;
    }

    /**
     * @return int
     */
    function getCode(): int
    {
        return $this->_code;
    }

    /**
     * @return mixed
     */
    function getData(): mixed
    {
        return $this->_data;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->_response;
    }
}