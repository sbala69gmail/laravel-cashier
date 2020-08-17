<?php
namespace App\Http\Requests;



use App\Http\Requests\BaseValidationRequest;
use Illuminate\Support\Facades\Hash;

class UserValidator extends BaseValidationRequest
{
    public $fields = [];
    public $quickAdd = [];
    public $fullAdd = [];
    public $messages = [];
    public $rules = [];

    public function __construct()
    {
        $this->messages['old_password_valid.min'] = 'Old password not matched';
        $this->rules = [
            'user_id' => ['required', 'string', 'exists:users,id', ],
            'username' => ['required', 'string', 'max:190' ],
            'password' => ['required', 'string', 'min:8', 'max:50'],
            'old_password_valid' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:190'],
            'location' => ['required', 'array', ],
            'location.*' => ['required', 'string', 'max:190', 'exists:locations,code'],
            'profile' => ['nullable', 'file', 'mimes:image', 'max:4096'],
            'roles' => ['required', 'array' ],
            'roles.*' => ['required', 'exists:roles,id' ],

            'email' => ['required', 'email', 'min:1', 'max:180'],
            'email_sent_type' => ['required', 'string', 'min:1', 'max:180', 'in:to,cc,bcc'],
            'email_type' => ['nullable', 'array' ],
            'email_type.*' => ['required', 'string', 'min:1', 'max:180', 'in:power,target'],
        ];
    }

    public function rules()
    {
        return $this->rules;
    }

    public function userLogin(array $data)
    {
        $checkField = [
            'username',
            'password',
        ];

        return $this->checkValidation($data, $checkField);
    }

    public function updatePassword(array $data, $user)
    {
        $this->rules['password'] = array_merge($this->rules['password'], ['confirmed', 'case_diff','numbers','letters','symbols']);
        $data['old_password_valid'] = 0;
        if (isset($data['old_password']) &&  Hash::check($data['old_password'], $user->password)) {
            $data['old_password_valid'] = 1;
        }

        $checkField = [
            'old_password',
            'password',
            'old_password_valid',
        ];
        return $this->checkValidation($data, $checkField);
    }

    public function store(array $data)
    {
        if($data['username']){
            $this->rules['username'] = ['required', 'string', 'max:190', 'unique:users,username,'.$data['username'].',username' ];
        }
        $checkField = [
            'username',
            'name',
            'location',
            'location.*',
            'profile',
            'roles',
            'roles.*',
            'email',
            'email_sent_type',
            'email_type',
            'email_type.*',
        ];

        return $this->checkValidation($data, $checkField);
    }

    public function update(array $data)
    {
        if($data['username']){
            $this->rules['username'] = ['required', 'string', 'max:190', 'unique:users,username,'.$data['username'].',username' ];
        }
        $checkField = [
            'user_id',
            'username',
            'name',
            'location',
            'location.*',
            'profile',
            'roles',
            'roles.*',
            'email',
            'email_sent_type',
            'email_type',
            'email_type.*',
        ];

        return $this->checkValidation($data, $checkField);
    }

    public function show(array $data)
    {
        $checkField = [
            'user_id',
        ];

        return $this->checkValidation($data, $checkField);
    }

    public function destroy(array $data)
    {
        $checkField = [
            'user_id',
        ];

        return $this->checkValidation($data, $checkField);
    }

    public function restore(array $data)
    {
        $checkField = [
            'user_id',
        ];

        return $this->checkValidation($data, $checkField);
    }

    public function reset(array $data)
    {
        $checkField = [
            'user_id',
        ];

        return $this->checkValidation($data, $checkField);
    }

}
