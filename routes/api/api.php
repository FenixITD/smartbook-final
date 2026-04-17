<?php

foreach (glob(__DIR__ . '/*.php') as $file) {
    if (realpath($file) !== __FILE__) {
        require $file;
    }
}
