<?php
require_once './../vendor/autoload.php';

use IkechukwuOkalia\LetterAvatar;

$avatar = new LetterAvatar('Ikechukwu Okalia Avatar', 'circle', 128, 3);
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Example</title>
</head>
<body>
    <img src="<?php echo $avatar;?>" alt="">
</body>
</html>