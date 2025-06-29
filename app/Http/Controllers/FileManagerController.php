<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

class FileManagerController extends Controller
{
    public function index()
    {
        return view('filemanager.index');
    }
}
