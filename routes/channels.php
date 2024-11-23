<?php

use Illuminate\Support\Facades\Broadcast;
// use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('tour-created', function ($user) {
    return true; // Cho phép mọi người dùng truy cập kênh này
});
// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return true;
});
