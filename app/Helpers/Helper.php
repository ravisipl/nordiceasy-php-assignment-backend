<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class Helper {

    // Sends a success response with optional token header.
    public static function sendSuccessResponse($data = [], $message = 'Something went wrong.', $token = '', $code = 200) {
        $response['success'] = true;
        $response['success_code'] = $code;
        if($message){
            $response['message'] = $message;
        }
        // $response['data'] =
        if($token){
            $response['token'] = $token;
        }
        $response['data'] = $data;

        if($token && $token != ''){
            return response()->json($response)->header('token', $token);
        }
        return response()->json($response, $code);
    }

    // Sends a failure response.
    public static function sendFailureResponse($message = 'Something went wrong.', $code = 200) {
        $response['success'] = false;
        $response['success_code'] = $code;
        $response['message'] = $message;
        return response()->json($response, $code);
    }

    // function for send email
    public static function sendEmail($template, $data, $toEmail, $toName, $subject, $ccEmails=[], $fromName = '', $fromEmail = '',$attachment = '', $mime =  '') {
        /*Log::info($fromEmail);*/
        // Comment for production
        // $toEmail = Helper::replaceWithEmailDomain($toEmail);
        // $ccEmails = Helper::replaceWithEmailDomainMultiple($ccEmails);

        $email_response = Mail::send($template, $data, function ($message) use($toEmail, $toName, $subject, $data, $fromName, $fromEmail, $ccEmails, $attachment , $mime) {
            $message->to($toEmail, $toName);
            $message->subject($subject);

            if ($fromEmail != '' && $fromName != '') {
                $message->from($fromEmail,  $fromName);
            }

            if (!empty($ccEmails)) {
                $message->cc($ccEmails);
            }

            if($attachment != ''){
                $message->attach($attachment,['as' => 'Result', 'mime' => $mime]);
            }
        });
    }

    // Generate Ticket number
    public static function generateTicketNo($supportDepName, $number){
        return $supportDepName . "-". date('Y') . "-" .date('m') . "-".str_pad($number, 4, '0', STR_PAD_LEFT);;
    }
    
    // Get role of auth user
    public static function getUserRole($user){
        $data = [];
        $roles = $user->roles;
        foreach ($roles as $role) {
            $data[] = $role->name; // Output each role name
        }
        // $data => ['user', 'hr_admin']
    
        $result = [];
        if(in_array(config('constants.ROLES.MD'), $data)){
            array_push($result, config('constants.ROLES.MD'));
        }
        if(in_array(config('constants.ROLES.CEO'), $data)){
            array_push($result, config('constants.ROLES.CEO'));
        }
        if(in_array(config('constants.ROLES.VH'), $data)){
            array_push($result, config('constants.ROLES.VH'));
        }
        if(in_array(config('constants.ROLES.PH'), $data)){
            array_push($result, config('constants.ROLES.PH'));
        }

        $admin = ['it_admin', 'hr_admin', 'infra_admin', 'finance_admin'];
        $manager = ['it_manager', 'hr_manager', 'infra_manager', 'finance_manager'];
        $resolver = ['it_resolver', 'hr_resolver', 'infra_resolver', 'finance_resolver'];
        $user = ['user'];
      
        foreach($admin as $r){
            if(in_array($r, $data)){
                array_push($result, $r);
            }
        }
        foreach($manager as $r){
            if(in_array($r, $data)){
                array_push($result, $r);
            }
        }
        foreach($resolver as $r){
            if(in_array($r, $data)){
                array_push($result, $r);
            }
        }
        foreach($user as $r){
            if(in_array($r, $data)){
                array_push($result, $r);
            }
        }
        foreach($data as $r){
            if(!in_array($r, $result)){
                array_push($result, $r);
            }
        }
        return $result;

    }

    public static function replaceWithEmailDomain($email){
        $parts = explode('@', $email);
        $username = $parts[0];

        return $username . '@yopmail.com';
    }

    
    public static function replaceWithEmailDomainMultiple($emails){
        $arr = [];
        foreach($emails as $email){

            $parts = explode('@', $email);
            $username = $parts[0];
            $arr[] = $username . '@yopmail.com';
        }

        return $arr;
    }

    // Formate resolvancy time
    public static function formateResolvancyTime($decimalTime){
        $carbonTime = Carbon::createFromTime((int)$decimalTime, (int)(($decimalTime - (int)$decimalTime) * 60));
        return $carbonTime->format('H:i');
    }


    
}
