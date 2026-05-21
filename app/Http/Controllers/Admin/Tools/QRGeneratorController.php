<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class QRGeneratorController extends Controller
{
    public function index()
    {

        return view('pages.admin.tools.qr-generator.index');
    }
}