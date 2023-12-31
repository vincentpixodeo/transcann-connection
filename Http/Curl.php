<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Http;

use CurlHandle;

class Curl
{
    const LOG = true;

    protected string $_userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';

    protected string $_baseUrl;

    protected bool $_followLocation;

    protected int $_maxRedirects;

    protected int $_timeout;

    protected string $_referer ="";

    protected string $_cookieFileLocation = './cookie.txt';

    protected array $_auth = [];
    protected string $_token = '';

    /**
     * @var CurlHandle|null
     */
    protected ?CurlHandle $_curl;

    protected ?string $_response;
    protected ?string $_error;
    protected ?int $_status;

    protected ?Log $currentLog;

    protected array $logs = [];

    /**
     * @return Log[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @return Log|null
     */
    public function getCurrentLog(): ?Log
    {
        return $this->currentLog;
    }

    public function __construct(
        string $baseUrl,
        bool   $followLocation = true,
        int    $timeOut = 30,
        int    $maxRedirects = 4
        )

    {

        $this->_baseUrl = trim($baseUrl, '/');

        $this->_followLocation = $followLocation;

        $this->_timeout = $timeOut;

        $this->_maxRedirects = $maxRedirects;

        $this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';

    }

    /**
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->_baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @param $user
     * @param $pass
     * @return $this
     */
    public function setAuth($user = null, $pass = null): static
    {
        if ($user && $pass) {
            $this->_auth = [
                'user' => $user,
                'pass' => $pass,
            ];
        } else {
            $this->_auth = [];
        }
        return $this;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->_token = $token;
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initCurl(): static
    {
        $ssl = stripos($this->_baseUrl,'https://') === 0;
        $this->_curl = curl_init();
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_AUTOREFERER => 1,
            CURLOPT_FOLLOWLOCATION => $this->_followLocation,
            CURLOPT_MAXREDIRS => $this->_maxRedirects,
            CURLOPT_USERAGENT => $this->_userAgent,
            CURLOPT_TIMEOUT => $this->_timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => ['Expect:'],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_REFERER => $this->_referer,
            CURLOPT_COOKIEJAR => $this->_cookieFileLocation,
            CURLOPT_COOKIEFILE => $this->_cookieFileLocation
        ];

        if ($ssl) {
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        curl_setopt_array($this->_curl, $options);
        return $this;
    }

    /**
     * @param string $uri
     * @param array $query
     * @param array $header
     * @return Response
     * @throws Exception
     */
    public function get(string $uri, array $query = [], array $header = []): Response
    {
        $this->_initCurl();

        if ($this->_token) {
            $query['token'] = $this->_token;
        }

        $url = $this->_baseUrl.'/'.trim($uri, '/'). ($query ? '?'.http_build_query($query, '') : '');

        curl_setopt($this->_curl,CURLOPT_URL, $url);

        if (self::LOG) {
            $this->currentLog = new Log('GET', $url, [], $header);
        }
        return $this->execute($header);
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @return Response
     * @throws Exception
     */
    public function delete(string $uri, array $data = [], array $header = []): Response
    {
        return $this->post($uri, $data, $header, "DELETE");
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @return Response
     * @throws Exception
     */
    public function put(string $uri, array $data = [], array $header = []): Response
    {
        return $this->post($uri, $data, $header, "PUT");
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @param string $customRequest value: POST|PUT|DELETE
     * @return Response
     * @throws Exception
     */
    public function post(string $uri, array $data = [], array $header = [], string $customRequest = "POST"): Response
    {
        $this->_initCurl();
        $url = $this->_baseUrl.'/'.trim($uri, '/');

        if ($this->_token) {
            $data['token'] = $this->_token;
        }

        curl_setopt_array($this->_curl,[
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_CUSTOMREQUEST => $customRequest,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        if (self::LOG) {
            $this->currentLog = new Log($customRequest, $url, $data, $header);
        }
        return $this->execute($header);
    }

    /**
     * @param array $header
     * @return Response
     * @throws Exception
     */
    function execute(array $header = []): Response
    {
        // set Header
        if ($header) {
            curl_setopt($this->_curl,CURLOPT_HEADER,true);
            curl_setopt($this->_curl,CURLOPT_HTTPHEADER, $header);
        }


        // set Auth
        if ($this->_auth) {
            curl_setopt($this->_curl, CURLOPT_USERPWD, $this->_auth['user'].':'.$this->_auth['pass']);
        }

        $this->_response = curl_exec($this->_curl);

        $this->_status = curl_getinfo($this->_curl,CURLINFO_HTTP_CODE);

        if (self::LOG) {
            $this->currentLog->setResponse($this->_response, $this->_status);
            $this->logs[] = $this->currentLog;
        }

        if (curl_errno($this->_curl)) {
            $this->_error = curl_error($this->_curl);
            throw new Exception($this->_error, $this->_status);
        }

        curl_close($this->_curl);

        return new Response($this->_response, $this->_status);
    }

    public function getHttpStatus()
    {
        return $this->_status;

    }

    public function __toString()
    {

        return $this->_response;

    }
}