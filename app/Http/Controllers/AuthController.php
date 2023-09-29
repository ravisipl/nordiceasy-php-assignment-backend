<?php

namespace App\Http\Controllers;

use DB;
use Crpyt;
use Exception;
use Validator;
use App\Models\Role;
use App\Models\User;
use App\Models\Status;
use App\Helpers\Helper;
use App\Models\Verticals;
use App\Jobs\SendEmailJob;
use App\Models\App;
use App\Models\Departments;
use App\Models\UserDetails;
use Illuminate\Support\Str;
use App\Models\Designations;
use Illuminate\Http\Request;
use App\Models\OfficeLocation;
use App\Models\PasswordResets;
use App\Models\PRPOStatus;
use App\Models\PrRequestType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use \Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;


class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

   
}
