<?php
$text = "ขอเชิญคุณ   $_GET[seq]   ที่   $_GET[ch]";

$text = substr($text, 0, 500);
$lang = "th";
$file = md5($lang . "?" . urlencode($text));
$file = "../audio/" . $file . ".mp3";

if (!is_dir("../audio/"))
    mkdir("../audio/");
else
if (substr(sprintf('%o', fileperms('../audio/')), -4) != "0777")
    chmod("../audio/", 0777);


if (!file_exists($file)) {
    $mp3 = file_get_contents(
            'http://translate.google.com/translate_tts?ie=UTF-8&q=' . urlencode($text) . '&tl=' . $lang . '&total=1&idx=0&textlen=5&prev=input');
    file_put_contents($file, $mp3);
}
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" lang="en-US">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" lang="en-US">
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html lang="en-US">
    <!--<![endif]-->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width">
    <body>
    zz
        <div align="center">
            <?php if (!empty($_GET[seq]) and !empty($_GET[ch])): ?>
                <audio controls="controls" autoplay="autoplay">
                    <source src="<?php echo $file; ?>" type="audio/mp3" />
                </audio>
            <?php endif; ?>
        </div>
</body>
</html>
<?php
//unlink("../audio/".$file);
?>