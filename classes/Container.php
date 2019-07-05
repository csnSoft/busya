<?php
/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 27.06.2019
 * Time: 11:15
 */

namespace CSN\busya\classes;

class Container
{
    private $containerItems = [];

    public function __get(string $name)
    {
        return $this->containerItems[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->containerItems) && !empty($this->containerItems[$name]);
    }

    public function add(string $itemName, $class, array $params = null)
    {
        $params['container'] = $this;
        if (is_object($class)) {
            $this->containerItems[$itemName] = $class;
        } else {
            $class = 'CSN\b24_application\classes\\'.$class;
            $this->containerItems[$itemName] = new $class($params);
        }
        return $this;
    }

    public function dell(string $name)
    {
        if ($this->has($name)) {
            unset($this->containerItems[$name]);
        }
        return $this;
    }
}
    //CSN\b24_application\classes\helpers\FileHelper