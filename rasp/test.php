<?php

$outGoingTime = strtotime('09:50:00') - strtotime('08:50:00');
echo gmdate("H:i:s", $outGoingTime);
