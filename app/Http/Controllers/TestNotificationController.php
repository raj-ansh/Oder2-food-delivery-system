<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Notifications\Notifiable;
use App\Notifications\InvoicePaid;
use Illuminate\Support\Facades\Notification;

use Illuminate\Http\Request;

class TestNotificationController extends Controller
{
    public function send(){
        $user = User::find(2);
        Notification::send($user, new InvoicePaid());
        echo 'success';
    }
}
