<?php
/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 27.06.2019
 * Time: 11:13
 */

namespace CSN\busya\classes\rest_exe;


class B24ListGetter
{
    public $method;
    public $params;
    public $result = [];
    private $container;

    public function __construct($params)
    {
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }

    public function setOption(string $key, $value)
    {
        $this->$key = $value;
        return $this;
    }

    public function setArrayOption(array $arr)
    {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    public function getList() // use method 'batch' from Bitrix24 REST-API
    {
        $exe = $this->container->restExequtor;

        $total = $exe->restExe($this->method, $this->params);

        $total = $total['total'];

        $calls = ceil($total / 50); $current_call = 0;

        $batch = array();

        do {
            $current_call++;

            $this->params['start'] = ($current_call - 1) * 50;

            $batch[$current_call-1] = $this->method . '?' . http_build_query($this->params);

            if ((count($batch) == 50) || ($current_call == $calls)) {
                $batch_result = $exe->restExe('batch', array('cmd' => $batch));
                $this->result[] = $batch_result;
                $batch = array();
            }

        } while ($current_call < $calls);

        return $this->result;
    }
}