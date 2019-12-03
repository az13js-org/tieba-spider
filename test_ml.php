<?php
require 'autoload.php';

function mb_string_to_array(string $str): array
{
    return preg_split('//u', $str, null, PREG_SPLIT_NO_EMPTY);
}

if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'config.php')) {
    $config = include __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
} else {
    $config = include __DIR__ . DIRECTORY_SEPARATOR . 'config.example.php';
}
$db = Connect::getInstance(
    $config['mysqlAddress'],
    $config['user'],
    $config['port'],
    $config['password'],
    $config['database']
);

$db->send('SELECT `post` AS `str` FROM `posts` ORDER BY `id` DESC LIMIT 1000');
$source = $db->recv();
unset($db);
foreach ($source as $k => $data) {
    $source[$k] = array_values(array_unique(mb_string_to_array($data['str'])));
}
$data = new Apriori\Apriori(0.1, 0.1, $source);
foreach ($data->getAssociationRule()->getAssociationPairs() as $pair) {
    echo implode(',', $pair->getFromItemSet()->getItems()) . '->' . implode(',', $pair->getToItemSet()->getItems()) . PHP_EOL;
}