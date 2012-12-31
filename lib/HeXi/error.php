<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>App Error - HeXi 2.0 Alpha</title>
    <style type="text/css">
        *{margin:0;padding:0;font-size:13px;font-family:Consolas,'Microsoft YaHei',Arial,sans-serif}
        h1{color:#992626;font-size:26px;padding:30px 20px 20px}
        h3{padding:13px 20px;border-top:1px solid #BBB;margin-top:13px;font-size:15px}
        ul{line-height:2.5em;padding-left:20px;list-style:none}
        p{line-height:2.5em;padding-left:20px}
    </style>
</head>
<body>
<h1><?php echo $message?></h1>
<h3>Trace</h3>
<?php
echo '<ul id="trace">';
foreach ($trace as $k => $t) {
    echo '<li>#' . ($k + 1) . '&nbsp;&nbsp;';
    if ($t['class']) {
        echo $t['class'] . $t['type'] . $t['function'];
    } else {
        echo $t['function'];
    }
    if ($t['file']) {
        echo '&nbsp;@&nbsp;' . $t['file'] . ':' . $t['line'];
    }
    echo '</li>';
}
echo '</ul>';
?>
<h3>SQL</h3>
<?php
if ($sql) {
    echo '<ul id="sql">';
    foreach ($sql as $k => $s) {
        echo '<li>#' . ($k + 1) . '&nbsp;&nbsp;' . $s . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p id="sql">没有数据库操作</p>';
}
?>
<h3>Statics</h3>
<p>Executed Time : <?php echo $time ?> ms; Memory Usage : <?php echo $memory ?> KB</p>
</body>
</html>