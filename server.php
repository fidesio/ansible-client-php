<?php

if (php_sapi_name() == 'cli-server') {
    if ($_SERVER['REQUEST_URI'] != '/') {
        return false;
    }
}
