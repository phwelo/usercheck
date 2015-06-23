<?php

/* our simple php ping function */
function ping($host)
{
        exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($host)), $res, $rval);
        return $rval === 0;
}

/* check if the host is up
        $host can also be an ip address */
$host = '10.4.12.16';
$up = ping($host);

/* optionally display either a red or green image to signify the server status */
echo ($up ? 'up' : 'down');
?>