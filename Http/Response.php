<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Http;

class Response
{
    protected string $_response;
    protected int $_code;
    protected $_data;

    /**
     * @throws Exception
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
     * @return mixed
     * @throws Exception
     */
    protected function _processData(bool $jsonException = true): mixed
    {
        if (is_null($this->_data)) {
            $return = json_decode($this->_response, true);

            if (json_last_error() && $jsonException) {
                throw new JsonException('json_decode: '. json_last_error_msg());
            }
            if (json_last_error() && !$jsonException) {
                $this->_data = $this->_response;
            } else {
                $this->_data = $return;
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
    function getData(): mixed
    {
        return $this->_data;
    }

}