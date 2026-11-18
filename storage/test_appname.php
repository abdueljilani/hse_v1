<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app()->setLocale("ar");
$r = __("messages.app_name");
file_put_contents(__DIR__ . "/out2.txt", $r);
echo "LEN=" . strlen($r) . "\n";
