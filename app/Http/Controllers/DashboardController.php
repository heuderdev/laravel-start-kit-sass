<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function indexss()
    {
        $notification = auth()->user()->unreadNotifications;
        $data = json_decode($notification, true);
        dd($data);
        return view('dashboard', compact('data'));
    }

    public function index()
    {
        $notifications = auth()->user()->unreadNotifications->map(function ($notification) {
            return [
                'id'         => $notification->id,
                'data'       => $notification->data, // já é array
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });


        return view('dashboard', compact('notifications'));
    }

    public function welcome()
    {
        return view('welcome');
    }
}
