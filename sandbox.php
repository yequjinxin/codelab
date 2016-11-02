<?php 

$name = isset($_GET['name']) ? $_GET['name'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type === 'php') {
    $file = "./app/sandbox/{$name}/output/{$name}.html";
} else if ($type === 'html') {
    $file = "./app/sandbox/{$name}/source/index.html";
}

if (file_exists($file)) {
    include $file;
} else {
    echo 'file not found';exit;
}

