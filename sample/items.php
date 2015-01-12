<?php 
require __DIR__.'/../vendor/autoload.php';

$accessToken = '__YOUR_TOKEN__';


$qiita = new Qiita\Qiita($accessToken);

$ret = $qiita->api('item.list', [
]);

var_dump($ret);
