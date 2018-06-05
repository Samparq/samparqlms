<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 14/9/17
 * Time: 5:15 PM
 */


use backend\assets\AppAsset;
AppAsset::register($this);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Privacy Policy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= Yii::getAlias('@web/images/favicon.ico')?>" type="image/x-icon" />
</head>
<body>

<?= $content ?>

</body>
</html>


