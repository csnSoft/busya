<?php
/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 24.06.2019
 * Time: 11:26
 *
 * ver 1.0
 */

namespace CSN\b24_application\classes\auth;

/**
 * Class B24AuthFactory
 * @package CSN\b24_application\classes\auth
 */
class B24AuthFactory
{
    private $options = [
        'method' => '', //название метода для создания экземпляра класса B24Auth
        'authData' => [], //данные для авторизации
        'afterRefreshFunction' => '' //callback-функция, которая будет вызвана после рефреша токенов. Void, на вход принимает экземпляр класса B24Auth
    ];
    private $container;

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        $this->container = $options['container'];
    }

    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function setArrayOption(array $arr)
    {
        foreach ($arr as $key => $value) {
            $this->options[$key] = $value;
        }
        return $this;
    }

    public function makeAuth()
    {
        if (!empty($this->options['method'])) {
            $method = $this->options['method'];
            return $this->$method();
        } else {
            if (array_key_exists('AUTH_ID', $this->options['authData'])){
                return $this->makeAuthType2FromArray();
            } else if (array_key_exists('access_token', $this->options['authData'])) {
                return $this->makeAuthType3FromArray();
            }
        }
        return false;
    }

    public function makeAuthType2FromArray()
    {
        $authData = $this->options['authData'];
        $res = [
            'access_token' => $authData['AUTH_ID'],
            'domain' => $authData['DOMAIN'],
            'refresh_token' => $authData['REFRESH_ID']
        ];
        if (!array_key_exists('refresh_token', $res)) {
            return false;
        }

        $params = [
            'container' => $this->container,
            'authData' => $res,
            'refreshFunc' => $this->options['afterRefreshFunction']
        ];

        $authClass = new B24Auth($params);

        return $authClass->refreshAuth();
    }

    public function makeAuthType3FromArray()
    {
        $authData = $this->options['authData'];
        $res = [];
        foreach ($authData as $key => $value) {
            $res[$key] = $value;
        }
        if (!array_key_exists('refresh_token', $res)) {
            return false;
        }

        $params = [
            'container' => $this->container,
            'authData' => $res,
            'refreshFunc' => $this->options['afterRefreshFunction']
        ];
        $authClass = new B24Auth($params);

        return $authClass->refreshAuth();
    }
}