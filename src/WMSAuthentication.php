<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent;

use Exception;
use WMS\Xtent\Apis\Login\GetToken;
use WMS\Xtent\Apis\Login\ReleaseToken;
use WMS\Xtent\Contracts\ClientInterface;
use WMS\Xtent\Http\Curl;
use WMS\Xtent\Http\Log;

class WMSAuthentication
{
    private static ?string $pathAuthFile = null;

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
        $instance = WmsXtentService::instance();
        $this->_client = $instance->getClient();
        self::$pathAuthFile = $instance->storagePath() . '/auth.json';

    }


    /**
     * @param bool $renew
     * @param bool $withException
     * @return string|null
     * @throws Exception
     */
    public function getToken(bool $renew = false, bool $withException = false): ?string
    {
        try {
            if ($renew && $this->getAuthenticationData(true)) {
                $this->releaseToken();
            }

            if ($authenticationData = $this->getAuthenticationData()) {
                return $authenticationData['token'];
            }
            return null;
        } catch (Exception $exception) {
            if ($withException) {
                throw $exception;
            }
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
     * @throws Exception
     */
    protected function doAuthentication(): bool
    {
        $this->releaseToken();

        $action = WmsXtentService::instance()->getAction(GetToken::class, [$this->_client]);
        if ($action->execute([
                'accessId' => $this->accessId,
                'userId' => $this->user,
                'password' => $this->password
            ]) && $action->getResponse()->getCode() == 200) {

            $this->setAuthenticationData($action->getResponse()->getData());
            return true;
        }
        if ($errors = $action->getErrors()) {
            throw $errors[0];
        }
        return false;
    }


    /**
     * @return $this
     * @throws Exception
     */
    protected function releaseToken(bool $withException = false): static
    {
        try {
            file_put_contents(self::$pathAuthFile, '');

            if ($this->_authenticationData) {
                WmsXtentService::instance()->getAction(ReleaseToken::class, [$this->_client])->execute([
                    'token' => $this->_authenticationData['token'],
                ]);
            }
        } catch (Exception $exception) {
            if ($withException) throw $exception;
        }

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

        file_put_contents(self::$pathAuthFile, json_encode($this->_authenticationData));
        return $this;
    }


    /**
     * @param bool $onlyCache
     * @return array|null
     */
    protected function getAuthenticationData(bool $onlyCache = false): ?array
    {
        $content = null;
        if (file_exists(self::$pathAuthFile)) {
            $content = file_get_contents(self::$pathAuthFile);
        }

        /** Fetch data from json file */
        if (is_null($this->_authenticationData) && $content) {
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
            $expiredAt = (new \DateTime())->setTimestamp($this->_authenticationData['expiredAt']);
            if ($now->diff($expiredAt)->invert) {
                /** release current Token */
                $this->releaseToken();
            } else {
                $this->setAuthenticationData($this->_authenticationData['token']);
            }
        }

        if (!$onlyCache && !$this->_authenticationData) {
            $this->doAuthentication();
        }

        return $this->_authenticationData;

    }

    function getClient(): ?ClientInterface
    {
        return $this->_client;
    }

}
