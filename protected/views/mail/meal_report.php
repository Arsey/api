<?php

$msg = "{$user['username']} ({$user['email']}) is reporting {$meal->name} ({$meal->id}) at {$restaurant->name} ({$restaurant->id}) in {$restaurant->city}";
if (!empty($restaurant->state)) {
    $msg.=", {$restaurant->state}.";
}
echo $msg;