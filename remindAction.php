<?php
$domainName = "www.rili.com";
$pass = "WA4BCHWGcCwBh8MyolEBIJjKtQq8wXWf";
$url = "http://".$domainName."/Home/Index/remindAction/pass/".$pass;

$res = file_get_contents($url);
die($res);

