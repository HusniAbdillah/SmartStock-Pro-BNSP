<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$notifs = DB::table('notifications')->orderBy('notifiable_id')->get();
echo count($notifs) . " notifikasi total\n\n";
foreach ($notifs as $n) {
    $data = json_decode($n->data, true);
    $user = User::find($n->notifiable_id);
    printf(
        "User: %-18s | %-12s | %s\n",
        ($user ? "{$user->name}" : "ID:{$n->notifiable_id}"),
        ($n->read_at ? 'dibaca' : 'BELUM DIBACA'),
        $data['title'] ?? '?'
    );
}
