<?php
require 'autoload.php';

function saveOrUpdateTiebaHotData(Connect $c, string $tieba, string $text, bool $isNew)
{
    $tb = addslashes($tieba);
    $h = addslashes($text);
    $c->send("SELECT `id` FROM `hots` WHERE `tieba_name`=\"$tb\" AND `hot`=\"$h\"");
    $exis = $c->recv();
    $now = date('Y-m-d H:i:s');
    $rank = $isNew ? 1 : 0;
    if (empty($exis)) {
        $c->send("INSERT INTO `hots`(`rank`,`tieba_name`,`hot`,`create_time`,`update_time`) VALUES ($rank,\"$tb\",\"$h\",\"$now\",\"$now\")");
    } else {
        $c->send("UPDATE `hots` SET `version`=`version`+1,`update_time`=\"$now\",`rank`=`rank`+$rank WHERE `id`={$exis[0]['id']}");
    }
}

function saveOrUpdateUserData(Connect $c, string $text, bool $isNew)
{
    $name = addslashes($text);
    $c->send("SELECT `id` FROM `users` WHERE `user_name`=\"$name\"");
    $exis = $c->recv();
    $now = date('Y-m-d H:i:s');
    $rank = $isNew ? 1 : 0;
    if (empty($exis)) {
        $c->send("INSERT INTO `users`(`rank`,`user_name`,`create_time`,`update_time`) VALUES ($rank,\"$name\",\"$now\",\"$now\")");
    } else {
        $c->send("UPDATE `users` SET `version`=`version`+1,`update_time`=\"$now\",`rank`=`rank`+$rank WHERE `id`={$exis[0]['id']}");
    }
}

function saveTiezi(Connect $c, string $tiezi, string $tieba)
{
    $content = addslashes($tiezi);
    $name = addslashes($tieba);
    $c->send("SELECT `id` FROM `posts` WHERE `tieba_name`=\"$name\" AND `post`=\"$content\"");
    $exis = $c->recv();
    $now = date('Y-m-d H:i:s');
    if (empty($exis)) {
        $c->send("INSERT INTO `posts`(`create_time`,`update_time`,`tieba_name`,`post`) VALUES (\"$now\",\"$now\",\"$name\",\"$content\")");
    } else {
        $c->send("UPDATE `posts` SET `version`=`version`+1,`update_time`=\"$now\" WHERE `id`={$exis[0]['id']}");
    }
}

function compareForLastTiebaHotDataAndFindNew(string $tiebaId, array $hot): array
{
    if (empty($hot)) {
        return [];
    }
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'tieba_' . $tiebaId . '.json';
    if (!is_file($file)) {
        file_put_contents($file, json_encode($hot));
        return [];
    }
    $last = json_decode(file_get_contents($file), true);
    $result = [];
    foreach ($hot as $posting) {
        if (!in_array($posting, $last)) {
            $result[] = $posting;
        }
    }
    file_put_contents($file, json_encode($hot));
    return $result;
}

function compareForLastAuthorsAndFindNew(array $authors): array
{
    if (empty($authors)) {
        return [];
    }
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'users.json';
    if (!is_file($file)) {
        file_put_contents($file, json_encode($authors));
        return [];
    }
    $last = json_decode(file_get_contents($file), true);
    $result = [];
    foreach ($authors as $author) {
        if (!in_array($author, $last)) {
            $result[] = $author;
        }
    }
    file_put_contents($file, json_encode($authors));
    return $result;
}

if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'config.php')) {
    $config = include __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
} else {
    $config = include __DIR__ . DIRECTORY_SEPARATOR . 'config.example.php';
}
$x = Connect::getInstance(
    $config['mysqlAddress'],
    $config['user'],
    $config['port'],
    $config['password'],
    $config['database']
);
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'test.log', date('Y-m-d H:i:s') . ' 开始' . PHP_EOL, FILE_APPEND);
$x->send(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'database.sql'));

$cookie = '';
$name = $config['tieba'];
$s = new Tieba($name, $cookie);
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'test.log', date('Y-m-d H:i:s') . ' 解析数据' . PHP_EOL, FILE_APPEND);
$posts = $s->getAllText();
foreach ($s->getTiebaHot() as $k => $v_arr) {
    /* $k 对应第几个贴吧，$v_arr 贴吧的热铁数组 */
    $newHotPosting = compareForLastTiebaHotDataAndFindNew($k, $v_arr);
    foreach ($v_arr as $kk => $v) {
        saveOrUpdateTiebaHotData($x, $name[$k], $v, in_array($v, $newHotPosting));
    }
    foreach ($posts[$k] as $tiezi) {
        saveTiezi($x, $tiezi, $name[$k]);
    }
}

$allAuthors = $s->getAuthors();
$newAuthors = compareForLastAuthorsAndFindNew($allAuthors);
foreach ($allAuthors as $k => $v) {
    saveOrUpdateUserData($x, $v, in_array($v, $newAuthors));
}

$idle = mt_rand(1, 5 * 60);
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'test.log', date('Y-m-d H:i:s') . ' 暂停 ' . $idle . ' 秒' . PHP_EOL, FILE_APPEND);
sleep($idle);