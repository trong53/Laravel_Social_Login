<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

use App\Rules\{EmailRegex, NameRegex};

// We define the resource of User for API Service

class UserController extends Controller
{

    // API : Get all users
    public function index(Request $request)
    {
        try {
            $where = [];

            if ($request->name) $where[] = ['name', 'like', '%'.$request->name.'%'];
            if ($request->email) $where[] = ['email', 'like', '%'.$request->email.'%'];

            if (!empty($where)) {
                $users = User::where($where)->get();
            } else {
                $users = User::all();
                // $users = User::paginate(5);
            }
            
            if ($users->count() > 0) {

                // $users = new UserCollection($users);  // if users is a collection, we have to use UserResource::Collection()

                $request->merge(['status'=>'succes']);     // cach nay OK, dc Intelliphens chap nhap
                // $request->status = 'success';            // cach nay van hoat dong. Nhung intelliphen ko chap nhan 
                                                            // boi vi ko co property 'status'
                // $users = new UserCollection($users);
                $users = new UserCollection($users, 200);
                return $users;

                $response = [
                    'number of responses'   => $users->count(),
                    'status'                => 'success',
                    'data'                  => $users,
                ];
            } else {
                $response = [
                    'status'    => 'There is no user.'
                ];
            }
      
        } catch (\Exception $e) {
            $response = [
                'status'    => 'errors',
                'message'   => $e->getMessage()
            ];
        }
        return $users;
    }


    // API : Get 1 user
    public function detail($id)
    {
        try {

            $user = User::find($id);

            if (!empty($user)) {

                // $user = new UserResource($user);  // if users is not a collection (simple array), we have to use new UserResource()
                $user = User::where('id', $id)->get();  // phai la 1 collection thi moi co the su dung dc relation voi Posts
                $user = new UserCollection($user);  // UserCollection o day ko call dc class UserResource.
                return $user;                       // no tra ve tat ca fields cua user.
                                                    // UserCollection chi hoat dong doi voi User la 1 collection. 
                                                    // Su dung all() hay get() thay vi find()
                $response = [
                    'status'    => 'success',
                    'data'      => $user,
                ];
            } else {
                $response = [
                    'status'    => 'There is no user.'
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'status'    => 'errors',
                'message'   => $e->getMessage()
            ];
        }
        return $response;
    }


    // API : Create 1 user
    public function create(Request $request)
    {
        $request->validate([
            'name'      => ['required', new NameRegex],
            'email'     => ['required', 'email', new EmailRegex, 'unique:users,email'],
            'password'  => ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()]
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        if (!empty($user)) {
            $response = [
                'status'    => '201 Success',
                'data'      => $user
            ];
        } else {
            $response = [
                'status'        => 'errors',
                'error-message' => 'Failed to save the user in database. Please try again.'
            ];
        }

        return $response;
    }


    // API : update 1 user with PUT and PATCH methods
    public function update(Request $request, $id)
    {
        $user = User::find($id);
            
        if (empty($user)) {
            return [
                'status'    => 'There is no user.'
            ];
        }

        // PUT method
        if ($request->method() == 'PUT') {

            $request->validate([
                'name'      => ['required', new NameRegex],
                'email'     => ['required', 'email', new EmailRegex, 'unique:users,email,'.$id],
                'password'  => ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()]
            ]);
            
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $status = $user->save();

            if (!$status) {
                return [
                    'status'    => 'errors',
                    'message'   => 'Failed to update the user in database. Please try again.' 
                ];
            }

            return [
                'status'    => 'success',
                'data'      => $user
            ];
        }


        // PATCH method
        if ($request->method() == 'PATCH') {

            $request->validate([
                'name'      => ['nullable', new NameRegex],
                'email'     => ['nullable', 'email', new EmailRegex, 'unique:users,email,'.$id],
                'password'  => ['nullable', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()]
            ]);

            if (!empty($request->name)) $user->name = $request->name;
            if (!empty($request->email)) $user->email = $request->email;
            if (!empty($request->password)) $user->password = Hash::make($request->password);

            $status = $user->save();

            if (!$status) {
                return [
                    'status'    => 'errors',
                    'message'   => 'Failed to update the user in database. Please try again.' 
                ];
            }

            return [
                'status'    => 'success',
                'data'      => $user
            ];
        }
    }


    // API : delete 1 user
    public function delete($id)
    {
        $user = User::find($id);
            
        if (empty($user)) {
            return [
                'status'    => 'There is no user.'
            ];
        }

        $status = $user->delete();

        if (!$status) {
            return [
                'status'    => 'errors',
                'message'   => 'Failed to delete the user. Please try again.' 
            ];
        }

        return [
            'status'    => 'success',
            'message'   => 'The user, id = '.$id.', has been deleted.'
        ];

    }
}
