<?php
/**
 * Created by PhpStorm.
 * User: Nik
 * Date: 21.06.2019
 * Time: 9:45
 */


namespace CSN\b24_application\classes\helpers;
/**
 * Class FileHelper
 * @package CSN\b24_application\classes\helpers
 */
class FileHelper
{
    public function readFromFile(string $path)
    {
        $file = fopen($path, 'r');
        $res = fread($file, filesize($path));
        fclose($file);
        return $res;
    }

    public function overwriteFile(string $path, string $data = null)
    {
        $file = fopen($path, 'w+');
        fwrite($file, $data);
        fclose($file);
    }

    public function addToFile(string $path, string $data)
    {
        $file = fopen($path, 'a+');
        fwrite($file, $data);
        fclose($file);
    }
}