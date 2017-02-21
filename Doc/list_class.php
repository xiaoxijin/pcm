<?php


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>文件列表<?php echo ' | '.PRODUCT_NAME;?></title>
    <link rel="stylesheet" href="assets/css/semantic.min.css">
    <link rel="stylesheet" href="assets/css/icon.min.css">
</head>
<body>
<div class="ui large top fixed menu transition visible" style="display: flex !important;">
    <div class="ui container">
        <div class="header item">API_DOC<code>(1.0)</code></div>
        <a class="active item" href="list_class.php">文件列表</a>
        <a class="item">接口列表</a>
        <a class="item">文档详情</a>
        <a class="item" href="wiki.php">使用说明</a>
    </div>
</div>
<div class="ui text container" style="max-width: none !important;margin-top: 50px;">
    <div class="ui floating message">
        <h1 class="ui header">文件列表</h1>
        <table class="ui black celled striped table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>文件名称</th>
                    <th>最后修改时间</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (!empty($files)) {
                    $num = 1;
                    foreach ($files['name'] as $k => $v) {
                        $NO = $num++;
                        echo '<tr>';
                        echo '<td>'.$NO.'</td>';
                        echo '<td>';
                        if ($files['type'][$k] == 'file') {
                            echo '<i class="file icon"></i> <a href="list_method.php?f='.$v.'">'.$v.'</a>';
                        } elseif ($files['type'][$k] == 'dir') {
                            echo '<i class="folder icon"></i> <a href="list_class.php?d='.$v.'">'.$v.'</a>';
                        }
                        echo '</td>';
                        echo '<td>'.date('Y-m-d H:i:s', $files['time'][$k]).'</td>';
                        echo '</tr>';
                    }
                }
            ?>
            </tbody>
        </table>
    </div>
    <p><?php echo COPYRIGHT?><p>
</div>
</body>
</html>
