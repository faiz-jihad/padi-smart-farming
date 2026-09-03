<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Telemetri Cuaca & Sensor Tanah Real-Time (WebSockets)
Broadcast::channel('agri-telemetry', function () {
    return true;
});

// Broadcast Peringatan Dini & Bencana Alam (WebSockets)
Broadcast::channel('disaster-alerts', function () {
    return true;
});
