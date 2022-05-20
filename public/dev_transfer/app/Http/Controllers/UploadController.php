<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class UploadController extends BaseController
{
    public function index(){
    	return view('index');
    }
}
