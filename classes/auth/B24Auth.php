<?php
/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 21.06.2019
 * Time: 17:29
 */

namespace CSN\busya\classes\auth;

class B24Auth
{
    private $authData = [];
    private $refreshFunc;
    private $container;

    public function __construct(array $params)
    {
        $this->container = $params['container'];
        if (!array_key_exists('authData', $params) || empty($params['authData'])) {
            throw new \Exception('Нет данных для создания экземпляра сласса B24Auth');
        }
        $this->authData = $params['authData'];
        if (array_key_exists('refreshFunc', $params)) {
            $this->setRefreshFunc($params['refreshFunc']);
        }
    }

    public function setRefreshFunc(callable $func = null)
    {
        $this->refreshFunc = $func;
        return $this;
    }

    public function getDomain()
    {
        if (array_key_exists('domain', $this->authData)) {
            return $this->authData['domain'];
        }
        return '';
    }

    public function getAccessToken()
    {
        if (array_key_exists('access_token', $this->authData)) {
            return $this->authData['access_token'];
        }
        return '';
    }

    public function getRefreshToken()
    {
        if (array_key_exists('refresh_token', $this->authData)) {
            return $this->authData['refresh_token'];
        }
        return '';
    }

    public function refreshAuth()
    {
        $queryUrl = 'https://'.$this->getDomain().'/oauth/token/';
        $queryData = http_build_query($queryParams = array(
            'grant_type' => 'refresh_token',
            'client_id' => APP_ID,
            'client_secret' => APP_SECRET_CODE,
            'refresh_token' => $this->getRefreshToken()
        ));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl.'?'.$queryData,
        ));

        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result, 1);

        $this->authData = $result;

        if (!is_null($this->refreshFunc)) {
            $func = $this->refreshFunc;
            $func($this);
        }
        return $this;
    }
}