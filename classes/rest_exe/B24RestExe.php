<?php

/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 24.06.2019
 * Time: 15:37
 */

namespace CSN\busya\classes\rest_exe;


class B24RestExe
{
    private $currentCallCounter = 0;
    private $container;

    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }

    public function setCurrentCallCounter(int $value)
    {
        $this->currentCallCounter = $value;
        return $this;
    }

    public function getCurrentCallCounter(): int
    {
        return $this->currentCallCounter;
    }

    public function restExe(string $method, array $params)
    {
        $auth = $this->container->auth;
        if (!is_a($auth, '\CSN\b24_application\classes\auth\B24Auth')) {
            throw new \Exception('Нееврный класс авторизации. Необходим экземпляр класса B24Auth');
        }

        $this->currentCallCounter += 1;

        if ($this->currentCallCounter == 2) {
            sleep(1);
            $this->currentCallCounter = 0;
        }

        $queryUrl = 'https://' . $auth->getDomain() . '/rest/' . $method . '.json';
        $queryData = http_build_query(array_merge($params, array("auth" => $auth->getAccessToken())));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ));

        $result = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (array_key_exists('error', $result) && $result['error'] == 'expired_token') {
            $auth->refreshAuth();
            $this->restExe($method, $params);
        }
        return $result;
    }
}