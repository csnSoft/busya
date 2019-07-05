<?php
/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 25.06.2019
 * Time: 17:19
 */

namespace CSN\busya\classes\rest_exe;

/**+
 * Class BatchMaker
 * @package CSN\busya\classes\rest_exe
 */
class BatchMaker
{
    private $requestsPool = [];
    private $batchParamsPool = [];
    private $sortFunc;
    private $options = [
        'noSort' => false
    ];
    private $container;

    /**
     * BatchMaker constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (!array_key_exists('sortFunc', $params) || is_null($params['sortFunc'])) {
            $params['sortFunc'] = function ($a, $b) {
                $typeMap = [
                    'list' => 0,
                    'get' => 0,
                    'post' => 1,
                    'update' => 1,
                    'set' => 1,
                    'add' => 1
                ];
                $a['type'] = explode('.', $a['method']);
                $a['type'] = $typeMap[$a['type'][count($a['type']) - 1]];
                $b['type'] = explode('.', $b['method']);
                $b['type'] = $typeMap[$b['type'][count($b['type']) - 1]];
                return $b <=> $a;
            };
        }
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param array $arr
     * @return $this
     */
    public function setArrayOption(array $arr)
    {
        foreach ($arr as $key => $value) {
            $this->options[$key] = $value;
        }
        return $this;
    }

    /**
     * @param array $requestsPool
     * @return $this
     */
    public function setRequestsPool(array $requestsPool)
    {
        $this->requestsPool = $requestsPool;
        return $this;
    }

    /**
     * @param array $sortFunc
     * @return $this
     */
    public function setSortFunc(callable $sortFunc)
    {
        $this->sortFunc = $sortFunc;
        return $this;
    }

    /**
     * @return array
     */
    public function getBatchParamsPool(): array
    {
        return $this->batchParamsPool;
    }

    /**
     * @return $this
     */
    public function makeBatchParamsPool()
    {
        $sortFunc = $this->sortFunc;
        $res = $this->requestsPool;

        if ($this->options['noSort'] != true) {
            uasort($res, $sortFunc); // сортируем
        }

        $res = array_chunk($res, 50); //разбиваем по 50

        $res = array_map(function($pool){
            $new_pool = [];
            $counter = 0;
            foreach ($pool as $key => $value) {
                if (!array_key_exists('id', $value)) {
                    $value['id'] = $counter;
                    ++$counter;
                }
                $new_pool[$key.'_'.$value['id']] = $value;
            }
            return $new_pool;
        }, $res); // формируем ключ вида index_id (например index_socketId)

        $res = array_map(function($pool){
            $new_pool = [];
            foreach ($pool as $key => $value) {
                $new_pool[$key] = $value['method'] . '?' . http_build_query($value['params']);
            }
            return $new_pool;
        }, $res); // формируем запросы

        $this->batchParamsPool = $res;

        return $this;
    }
}