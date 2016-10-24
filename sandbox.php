<?php 

$id = isset($_GET['id']) ? $_GET['id'] : '';
$file = "./app/sandbox/output/{$id}.html";

if (file_exists($file)) {
    include $file;
}

