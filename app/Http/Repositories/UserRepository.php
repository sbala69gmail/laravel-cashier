<?php
namespace App\Http\Repositories;

use App\User;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\ReportValidationException;
use Illuminate\Validation\ValidationException;


class UserRepository
{

    public function userLogin($array, $request)
    {
        try {
            if (!Auth::attempt($array)) {
                throw new ReportValidationException('username or password is wrong!.');
            }
            $user = Auth::user();
            $user->last_login_at = Carbon::now();
            $user->last_login_ip = $request->getClientIp();
            $user->save();
            return $user;
        } catch (ReportValidationException $e) {
            $error = ValidationException::withMessages([
                'password' => [$e->getMessage()],
             ]);
             throw $error;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function logout()
    {
        Auth::logout();
        return null;
    }

    public function updatePassword($array, User $user)
    {
        try {
            if ($user) {
                $user->password = bcrypt($array['password']);
                $user->save();
            }
            return true;
        } catch (Exception $e) {
            throw new ReportValidationException($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function index($search)
    {
        try {
            return User::whereLike(['name', 'username' ], $search)->latest()->paginate();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function store($array)
    {
        try {
            $array['password'] = bcrypt('Password@123');
            $user = User::create($array);
            $this->subFields($array, $user);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function subFields($array, $user){
        if(isset($array['profile'])){
            $array['profile']->storeAs(
                'public', $user->id.'.jpg'
            );
        }
        $locations = [];
        if(isset($array['location'])){
            foreach($array['location'] as $location){
                $locations[] = ['location_code' => $location];
            }
        }
        $user->locationUsers()->sync($locations, ['location_code']);

        $roles = [];
        if(isset($array['roles'])){
            foreach($array['roles'] as $role){
                $roles[] = ['role_id' => $role, 'model_type' => 'App\\User'];
            }
        }
        $user->roleUsers()->sync($roles, ['role_id', 'model_type']);

        //Email preference
        $emails = [];
        if(isset($array['email_type'])){
            foreach($array['email_type'] as $type){
                $emails[] = ['sent_type' => $array['email_sent_type'], 'type' => $type];
            }
        }
        $user->emailPreferences()->sync($emails, ['type', 'sent_type']);
    }

    public function update($array)
    {
        try {
            $user = User::find($array['user_id']);
            if($user){
                $user->update($array);
                $this->subFields($array, $user);
            }
            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function show($array)
    {
        try {
            $user = User::find($array['user_id']);
            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function destroy($array)
    {
        try {
            $user = User::find($array['user_id']);
            if($user){
                $user->update(['password' => '', 'active' => 0]);
            }
            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function restore($array)
    {
        try {
            $user = User::find($array['user_id']);
            if($user){
                $user->update(['active' => 1]);
            }
            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function reset($array)
    {
        try {
            $user = User::find($array['user_id']);
            if($user){
                $user->update([
                    'password' => bcrypt('Password@123')
                ]);
            }
            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
