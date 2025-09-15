<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(){ //not in use
        $page_title = 'Page Login';
        $page_description = 'Some description for the page';
        $action = 'page_login';
        return view('page.login', compact('page_title', 'page_description','action'));
    }
}
