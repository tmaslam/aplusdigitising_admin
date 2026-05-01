<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sites = DB::table('sites')->get();
foreach ($sites as $s) {
    echo 'SITE: ' . $s->name . ' (legacy: ' . $s->legacy_key . ', slug: ' . $s->slug . ')' . PHP_EOL;
    echo '  domain: ' . ($s->primary_domain ?? 'null') . ', from: ' . ($s->from_email ?? 'null') . ', support: ' . ($s->support_email ?? 'null') . PHP_EOL;
    echo '  primary: ' . $s->is_primary . ', active: ' . $s->is_active . PHP_EOL;
}
$domains = DB::table('site_domains')->get();
foreach ($domains as $d) {
    echo 'DOMAIN: ' . $d->host . ' -> site_id=' . $d->site_id . ' (primary=' . $d->is_primary . ')' . PHP_EOL;
}
