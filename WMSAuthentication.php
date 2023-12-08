<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS;

use WMS\Apis\Login\GetToken;
use WMS\Apis\Login\ReleaseToken;
use WMS\Http\Curl;
use WMS\Http\Exception;
use WMS\Http\HttpRequest;
use WMS\Http\Log;

class WMSAuthentication
{
    const AUTH_FILE = __DIR__.'/auth.json';

    /**
     * @var array|null
     */
    protected ?array $_authenticationData = null;

    protected Curl|null $_client = null;


    public function __construct(
        protected string $accessId,
        protected string $user,
        protected string $password
    )
    {
        $baseUrl = WmsXtentService::instance()->getConfig('baseUrl');
        $this->_client = new Curl($baseUrl);
    }


    /**
     * @param bool $renew
     * @return string|null
     */
    public function getToken(bool $renew = false): ?string
    {
        try {
            if ($renew && $this->getAuthenticationData(true)) {
                $this->releaseToken();
            }

            if ($authenticationData = $this->getAuthenticationData()) {
                return $authenticationData['token'];
            }
            return null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return Log[]
     */
    public function getLogs(): array
    {
        return $this->_client->getLogs();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function doAuthentication(): bool
    {
        $this->releaseToken();

        $action = WmsXtentService::instance()->getAction(GetToken::class, [
            $this->_client,
            [
                'accessId' => $this->accessId,
                'userId' => $this->user,
                'password' => $this->password
            ]
        ]);
        if ($action->execute() && $action->getResponse()->getCode() == 200) {
            
            $this->setAuthenticationData($action->getResponse()->getData());
            return true;
        }
        return false;
    }


    /**
     * @return $this
     */
    protected function releaseToken(): static
    {
        try {
            file_put_contents(self::AUTH_FILE, '');

            if ($this->_authenticationData) {
                WmsXtentService::instance()->getAction(ReleaseToken::class, [
                    $this->_client,
                    [
                        'token' => $this->_authenticationData['token'],
                    ]
                ])->execute();
            }
        } catch (\Exception $exception) {}

        /** reset authentication Data */
        $this->_authenticationData = null;
        return $this;
    }


    /**
     * @param $token
     * @return $this
     */
    protected function setAuthenticationData($token): static
    {
        $expiredAt = (new \DateTime())->add(new \DateInterval('PT9M'))->getTimestamp();
        $this->_authenticationData = [
            'token' => $token,
            'expiredAt' => $expiredAt,
        ];

        file_put_contents(self::AUTH_FILE, json_encode($this->_authenticationData));
        return $this;
    }


    /**
     * @param bool $onlyCache
     * @return array|null
     */
    protected function getAuthenticationData(bool $onlyCache = false): ?array
    {
        /** Fetch data from json file */
        if (is_null($this->_authenticationData) && $content = file_get_contents(self::AUTH_FILE)) {
            $data = json_decode($content, true);
            if (!empty($data['token']) && !empty($data['expiredAt'])) {
                $this->_authenticationData = [
                    'token' => $data['token'],
                    'expiredAt' => $data['expiredAt'],
                ];
            }
        }

        /** Validation the authentication*/
        if ($this->_authenticationData) {
            $now = new \DateTime();
            $expiredAt =  (new \DateTime())->setTimestamp($this->_authenticationData['expiredAt']);
            if ($now->diff($expiredAt)->invert) {
                /** release current Token */
                $this->releaseToken();
            }
        }

        if (!$onlyCache && !$this->_authenticationData) {
            $this->doAuthentication();
        }

        return $this->_authenticationData;

    }

    function getClient(): ?Curl
    {
        return $this->_client;
    }

}
