<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app()->setLocale("ar");
$out = view("layouts.app")->render();
file_put_contents(__DIR__ . "/out.html", $out);
