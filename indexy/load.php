<?php

include 'libs/mustache.php';
include 'libs/markdown.php';
include 'libs/smartypants.php';
include 'config.php';
include 'indexy.php';

$i = new Indexy($config);
echo $i->render();