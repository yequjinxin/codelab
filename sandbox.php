<?php 

$name = isset($_GET['name']) ? $_GET['name'] : '';
$file = "./app/sandbox/{$name}/output/{$name}.html";

if (file_exists($file)) {
    include $file;
} else {
    echo 'file not found';exit;
}

