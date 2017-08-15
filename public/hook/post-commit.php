<?php

echo exec("cd /pro/pzjhw/appapi.pzjhw.com && sudo bash .git/hooks/post-receive", $output);
var_dump($output);
?>