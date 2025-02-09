<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use App\Notifications\ReSetPasswordNotification;
use App\Traits\GeneralTrait;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use GeneralTrait;
    public function login(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'exists:users,email'],
                'password' => ['required', 'min:8']
            ]);

            if ($validator->fails()) {
                return $this->returnError(400,"خطأ في المدخلات.");
            }

            $user=User::where('email',$request->email)->first();

            if(!Hash::check($request->password,$user->password)){
                return $this->returnError(400,"لايوجد تطابق.");
            }

            $user->tokens()->delete();
            $token=$user->createToken($user->name)->plainTextToken;

            if($user->email_verified_at == null){
                $user->notify(new EmailVerificationNotification());
            }

            return $this->returnData('data',['token'=>$token,'user'=>$user]);
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    public function register(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name'=> 'required',
                'email' => ['required','email','unique:users,email'],
                'password' => ['required', 'min:8']
            ],[
                'name.required'=>'الاسم مطلوب.',
                'email.required'=>'البريد مطلوب.',
                'email.email'=>'البريد الالكتروني غير صالح.',
                'email.unique'=>'البريد الالكتروني مسجل بالفعل.',
                'password.required'=>'كلمة السر مطلوبة.',
                'password.min'=>'كلمة السر يجب ان تكون اكثر من 8 احرف.',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $user=new User();
            $user->name=$request->name;
            $user->email=$request->email;
            $user->password=Hash::make($request->password);
            $user->save();

            $token=$user->createToken($user->name)->plainTextToken;

            $user->notify(new EmailVerificationNotification());

            return $this->returnData('data',['token'=>$token,'user'=>$user]);

        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    public function logout(){
        auth('sanctum')->user()->tokens()->delete();

        return $this->returnSuccessMessage("تم تسجيل الخروج!");
    }

    public function emailVerification(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'otp' => ['required', 'max:6']
            ],[
                'otp.required'=>'الرمز مطاوب.',
                'otp.max'=>'الرمز اطول من اللازم.',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $user = auth('sanctum')->user();

            $otp = new Otp();
            $otpValidation = $otp->validate($user->email,$request->otp);

            if(!$otpValidation->status){
                return $this->returnError(401,$otpValidation->message);
            }

            $user->email_verified_at = now();
            $user->save();

            return $this->returnSuccessMessage("تم التاكيد بنجاح!");
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    public function reSendEmailVerification(){
        $user=auth('sanctum')->user();
        $user->notify(new EmailVerificationNotification());

        return $this->returnSuccessMessage('تم اعداة الارسال بنجاح!');
    }

    public function recoverPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required','email','exists:users,email'],
        ],[
            'email.required'=>'البريد مطلوب.',
            'email.email'=>'البريد الالكتروني غير صالح.',
            'email.exists'=>'البريد الالكتروني غير موجود.',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        $user=User::where('email',$request->email)->first();

        $user->notify(new ReSetPasswordNotification());

        return $this->returnSuccessMessage("تم ارسال الرمز بنجاح!");
    }

    public function recoverPasswordVerification(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required','email','exists:users,email'],
            'otp' => ['required', 'max:6'],
            'password' => ['required', 'min:8'],
        ],[
            'email.required'=>'البريد مطلوب.',
            'email.email'=>'البريد الالكتروني غير صالح.',
            'email.exists'=>'البريد الالكتروني غير موجود.',
            'otp.required'=>'الرمز مطاوب.',
            'otp.max'=>'الرمز اطول من اللازم.',
            'password.required'=>'كلمة السر مطلوبة.',
            'password.min'=>'كلمة السر يجب ان تكون اكثر من 8 احرف.',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        $otp = new Otp();
        $otpValidation = $otp->validate($request->email,$request->otp);

        if(!$otpValidation->status){
            return $this->returnError(401,$otpValidation->message);
        }

        $user=User::where('email',$request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return $this->returnSuccessMessage("تم تغيير كملة المرور بنجاح!");
    }


    public function checkTokenValidate(Request $request){
        return $this->returnSuccessMessage("validate");
    }
}
