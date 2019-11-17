<?php
require '../autoload.php';

if (is_file('../config.php')) {
    $config = include '../config.php';
} else {
    $config = include '../config.example.php';
}

$connect = Connect::getInstance(
    $config['mysqlAddress'],
    $config['user'],
    $config['port'],
    $config['password'],
    $config['database']
);
$connect->send('SELECT user_name,create_time FROM users ORDER BY create_time DESC LIMIT 20');
$users = $connect->recv();

$connect->send('SELECT count(*) AS `total` FROM users');
$totalQuery = $connect->recv();

$connect->send('SELECT tieba_name,hot,create_time FROM `hots` ORDER BY create_time DESC LIMIT 20');
$hots = $connect->recv();
$connect->send('SELECT count(*) AS `total` FROM `hots`');
$hotTotalQuery = $connect->recv();

$connect->send('SELECT tieba_name FROM `hots` GROUP BY tieba_name');
$tieba = $connect->recv();
?><html>
<head>
    <meta charset="utf-8"/>
    <meta name="referrer" content="never">
    <title>活跃信息</title>
    <style>
    body {
        color: #9B9B9B;
        background-color: #000000;
    }
    a {
        text-decoration: none;
        color: #00C400;
    }
    table,table tr th, table tr td {
        border:1px solid #2B2B2B;
    }
    table {
        border-collapse: collapse;
    }
    .text-center {
        text-align: center;
    }
    </style>
</head>
<body>
    <h2>最新获得的部分贴吧用户</h2>
    <table>
        <tr><th>用户名</th><th>获得时间</th></tr>
        <?php foreach ($users as $user) { ?>
        <tr>
            <td class="text-center"><a href="http://tieba.baidu.com/home/main?un=<?php echo urlencode($user['user_name']); ?>" target="_blank"><?php echo $user['user_name']; ?></a></td>
            <td class="text-center"><?php echo $user['create_time']; ?></td>
        </tr>
        <?php } ?>
    </table>
    <p>当前已获取的用户数：<?php echo $totalQuery[0]['total'] ?? 0; ?>。</p>

    <h2>最新获得的部分热贴</h2>
    <table>
        <tr><th>帖子名称</th><th>所属贴吧</th><th>获得时间</th></tr>
        <?php foreach ($hots as $hot) { ?>
        <tr>
            <td><a href="http://tieba.baidu.com/f/search/res?ie=utf-8&amp;qw=<?php echo urlencode($hot['hot']); ?>" target="_blank"><?php echo $hot['hot']; ?></a></td>
            <td class="text-center"><a href="http://tieba.baidu.com/f?ie=utf-8&amp;kw=<?php echo urlencode($hot['tieba_name']); ?>&amp;fr=search" target="_blank"><?php echo $hot['tieba_name']; ?></a></td>
            <td class="text-center"><?php echo $hot['create_time']; ?></td>
        </tr>
        <?php } ?>
    </table>
    <p>当前已获取的热贴数：<?php echo $hotTotalQuery[0]['total'] ?? 0; ?>。</p>

    <h2>数据源</h2>
    <p>
        <ul>
            <?php foreach ($tieba as $name) { ?>
            <li><a href="http://tieba.baidu.com/f?ie=utf-8&amp;kw=<?php echo urlencode($name['tieba_name']); ?>&amp;fr=search" target="_blank"><?php echo $name['tieba_name']; ?></a></li>
            <?php } ?>
        </ul>
    </p>
</body>
</html>