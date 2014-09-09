<?php
$text = "ขอเชิญคุณ   $_POST[seq]   ที่ห้องตรวจ   $_POST[ch]";

$text = substr($text, 0, 500);
$lang = "th";
$file = md5($lang . "?" . urlencode($text));
$file = "../audio/" . $file . ".mp3";

if (!is_dir("audio/"))
    mkdir("audio/");
else
if (substr(sprintf('%o', fileperms('../audio/')), -4) != "0777")
    chmod("../audio/", 0777);


if (!file_exists($file)) {
    $mp3 = file_get_contents(
            'http://translate.google.com/translate_tts?ie=UTF-8&q=' . urlencode($text) . '&tl=' . $lang . '&total=1&idx=0&textlen=5&prev=input');
    file_put_contents($file, $mp3);
}
?>