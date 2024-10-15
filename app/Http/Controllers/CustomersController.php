<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RedirectIfCustomer;
use App\Http\Middleware\RedirectIfNotParmitted;
use App\Models\City;
use App\Models\Country;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomersController extends Controller {
    public function __construct(){
        $this->middleware(RedirectIfNotParmitted::class.':customer');
    }
    public function index(){
        $customerRole = Role::where('slug', 'customer')->first();
        return Inertia::render('Customers/Index', [
            'title' => 'Contacts',
            'filters' => Request::all(['search']),
            'users' => User::orderBy('organization_id')
                ->whereRoleId($customerRole ? $customerRole->id : 0)
                ->filter(Request::all(['search']))
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'city' => $user->city,
                    'country' => $user->country_id ? $user->country->name: null,
                    'organization' => $user->organization ? $user->organization->customer_no.' '.$user->organization->name.' ' .$user->organization->name_en: null,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'role_id' => $user->role_id,
                    'photo' => $user->photo_path,
                    'created_at' => $user->created_at,
                ]),
        ]);
    }

    public function create(){
        return Inertia::render('Customers/Create',[
            'title' => 'Create a new contact',
            'organizations' => Organization::orderBy('customer_no')
              
            ->get()
            ->map(function ($organization) {
                return [
                    'id' => $organization->id,
                    'name' => '('.$organization->customer_no . ')' .$organization->name . ' ' . $organization->name_en,
                ];
            }),
            'countries' => Country::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name')
        ]);
    }

    public function store(){
        $userRequest = Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['nullable'],
            'phone' => ['nullable', 'max:25'],
            'organization_id' => ['required',Rule::exists('organizations', 'id')],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')],
            'password' => ['nullable'],
            'city' => ['nullable'],
            'address' => ['nullable'],
            'country_id' => ['nullable'],
            'role_id' => ['nullable'],
         

        ]);
        if(Request::file('photo')){
            $userRequest['photo_path'] = Request::file('photo')->store('users');
        }

        $customerRole = Role::where('slug', 'customer')->first();
        if(empty($userRequest['role_id']) && !empty($customerRole)){
            $userRequest['role_id'] = $customerRole->id;
        }
        User::create($userRequest);

        return Redirect::route('customers')->with('success', 'Contact created.');
    }

    public function edit(User $user)
    {
        
        $access =auth()->user()->access;
        
        if (!($access['customer']['update'] )) {
            return Redirect::back()->with('error', 'Unauthorized action..');
        }
        $can_delete = 0;
        $logged_user = Auth()->user();
        if($logged_user['role']['slug'] === 'admin'){
            $can_delete = 1;
        }
        return Inertia::render('Customers/Edit', [
            'title' => $user->name,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'can_delete' => $can_delete,
                'address' => $user->address,
                'country_id' => $user->country_id,
                'photo_path' => $user->photo_path,
                'organization_id' => $user->organization_id,
                'customer_no'=>$user->customer_no
            ],
            'countries' => Country::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
               'organizations' => Organization::orderBy('customer_no')
              
                ->get()
                ->map(function ($organization) {
                    return [
                        'id' => $organization->id,
                        'name' => '('.$organization->customer_no . ')' .$organization->name . ' ' . $organization->name_en,
                    ];
                }),
            'cities' => City::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name')
        ]
    
    );
    }

    public function update(User $user)
    {
        if (config('app.demo')) {
            return Redirect::back()->with('error', 'Updating customer is not allowed for the live demo.');
        }
        $access =auth()->user()->access;
        
        if (!($access['customer']['update'] )) {
            return Redirect::back()->with('error', 'Unauthorized action..');
        }
        Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['nullable'],
            'phone' => ['nullable', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable'],
            'city' => ['nullable'],
            'address' => ['nullable'],
            'country_id' => ['nullable'],
            'role_id' => ['nullable'],
            'photo' => ['nullable', 'image'],
            'organization_id' => ['required',
                Rule::exists('organizations', 'id'),
            ],
  
        ]);

        $user->update(Request::only('first_name', 'last_name', 'phone', 'email', 'city', 'address', 'country_id', 'role_id','organization_id'));

        if(Request::file('photo')){
            if(isset($user->photo_path) && !empty($user->photo_path) && File::exists(public_path($user->photo_path))){
                File::delete(public_path($user->photo_path));
            }
            $user->update(['photo_path' => '/files/'.Request::file('photo')->store('users', ['disk' => 'file_uploads'])]);
        }

        if (Request::get('password')) {
            $user->update(['password' => Request::get('password')]);
        }

        return Redirect::back()->with('success', 'Contact updated.');
    }

    public function destroy(User $user)
    {
        if (config('app.demo')) {
            return Redirect::back()->with('error', 'Deleting contact is not allowed for the live demo.');
        }

        $user->delete();
        return Redirect::route('customers')->with('success', 'Contact deleted.');
    }
    public function restore(User $user){
        $user->restore();
        return Redirect::back()->with('success', 'Contact restored!');
    }
}
