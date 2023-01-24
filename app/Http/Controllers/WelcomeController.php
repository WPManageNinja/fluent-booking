<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\Framework\Request\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Welcome to WPFluent.'
        ];
    }
}
