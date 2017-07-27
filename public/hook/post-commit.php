<?php

echo exec("ls",$file);
echo "</br>";
print_r($file);

/*$log = '';
$log .= '---------- SYNC'.PHP_EOL;
// $command = str_replace(array_keys($git), array_values($git), __CMD_SYNC__);
$command = 'cd /pro/pzjhw/appapi.pzjhw.com && mkdir test123';
echo '<hr/>EXECUTE COMMAND: '.$command.'<br/>';
$log .= 'Executing: '.$command.PHP_EOL;



shell_exec($command, $result).'<hr/>';
echo '* '.implode('<br/>* ', $result);
$log .= 'Result: '.PHP_EOL.'* '.implode(PHP_EOL.'* ', $result).PHP_EOL.PHP_EOL;*/

?>