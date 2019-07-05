<?
/*
 * перечень констант с ID приложения, ключем и др.
 */
define('APP_ID', 'local.5d0a42f5c656b3.06909733'); // take it from Bitrix24 after adding a new application
define('APP_SECRET_CODE', 'q3asKLod8VX7ISWWlcyRqMsXjb07I0CX13qzMWu79WiVoxMSod'); // take it from Bitrix24 after adding a new application
define('APP_REG_URL', 'https://app.insystema.ru/application_type_3/index.php'); // the same URL you should set when adding a new application in Bitrix24

/*
 * функция для автозагрузки классов
 * WARNING: данная функция автозагрузки не соответствует PSR-4! (в виду специфики немспейсов приложения)
 */
spl_autoload_register(function ($class) {
    $path = explode('\\', $class);
    //удалим из пути 'CNS' и 'busya'
    unset($path[0], $path[1]);
    $class = implode('/', $path) . '.php';
    require_once($class);
});

use CSN\busya\classes;
use CSN\busya\classes\auth;



// имитация больших запросов
$requestPool = [
    [
        'method' => 'crm.deal.list',
        'params' => [

            'filter' => [
                '>ID' => 50
            ],
            'select' => [
                'ID', 'TITLE'
            ],
            'start' => 0
        ],
        'id' => 1
    ],
    [
        'method' => 'crm.lead.list',
        'params' => [
            'filter' => [
                '>ID' => 500
            ]
        ],
        'id' => 2
    ],
    [
        'method' => 'crm.company.list',
        'params' => [
            'filter' => [
                '>ID' => 500
            ]
        ],
        'id' => 3
    ]
];
$arr = [];
for ($i = 0; $i < 1; $i++) {
    $rand_key = array_rand($requestPool, 1);
    $arr[] = $requestPool[$rand_key];
}

$container = new classes\Container();
$container->add('fileHelper', 'helpers\FileHelper');
$container->add('criptHelper', 'helpers\CriptHelper');
$container->add('authFactory', 'auth\B24AuthFactory');

$authData = $container->fileHelper->readFromFile('config.txt');
$authData = json_decode($authData, true);
foreach ($authData as $key => $value) {
    $authData[$key] = $container->criptHelper->crypt($value, true);
}
/*
 * Callable используется в случае, если после обновления действия необходимо совершить специфичное действие
 * (прим. записать в файлб логировать факт обновления токена и др.)
*/
$afterRefreshFunction = function (auth\B24Auth $authObj) use ($container) {
    $authData = [
        'access_token' => $container->criptHelper->crypt($authObj->getAccessToken()),
        'refresh_token' => $container->criptHelper->crypt($authObj->getRefreshToken()),
        'domain' => $container->criptHelper->crypt($authObj->getDomain())
    ];
    $authData = json_encode($authData);
    $container->fileHelper->overwriteFile('config.txt', $authData);
};
$auth = $container->authFactory->setArrayOption(
    [
        'authData' => $authData, //данные для авторизации
        'afterRefreshFunction' => $afterRefreshFunction //callback-функция, которая будет вызвана после рефреша токенов. Void, на вход принимает экземпляр класса B24Auth
    ]
)->makeAuth();

$container->add('auth', $auth);
$container->add('batchMaker', 'rest_exe\BatchMaker');
$container->add('restExequtor', 'rest_exe\B24RestExe');
$container->add('listGetter', 'rest_exe\B24ListGetter');

$list = $container->listGetter->setArrayOption([
    'method' => 'crm.deal.list',
    'params' => [
        'select' => [
            'ID'
        ]
    ]
])->getList();

echo '<pre>';
print_r($list);
echo '</pre>';
