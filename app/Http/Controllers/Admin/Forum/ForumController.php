<?php

namespace App\Http\Controllers\Admin\Forum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ForumController extends Controller
{
    public function index()
    {

        return view('pages.admin.forum.index');
    }
}