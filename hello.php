<?php

function read_file($path) {
    if ($handle = fopen($path, 'r')) {
        while (! feof($handle)) {
            yield trim(fgets($handle));
        }
        fclose($handle);
    }
}
$glob = read_file('url.txt');
while ($glob->valid()) {

    $line = $glob->current();

    echo $line.PHP_EOL;

    system("php test.php -u ".$line);

    echo PHP_EOL;

    $glob->next();
}

 ?>
