<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RedirectIfCustomer;
use App\Http\Middleware\RedirectIfNotParmitted;
use App\Models\City;
use App\Models\Country;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class OrganizationsController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfNotParmitted::class . ':organization');
    }

    public function index()
    {
  
        return Inertia::render('Organizations/Index', [
            'title' => 'Customers',
            'filters' => Request::all('search'),
            'organizations' => Organization::orderBy('customer_no')
                ->filter(Request::only('search'))
                ->paginate(8)
                ->withQueryString()
                ->through(function ($organization) {
                    return [
                        'id' => $organization->id,
                        'name' => $organization->name,
                        'name_en' => $organization->name_en,
                        'phone' => $organization->phone,
                        'city' => $organization->city,
                        'customer_no' => $organization->customer_no,
                    ];
                }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Organizations/Create', [
            'title' => 'Create a new customer',
            'countries' => Country::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'cities' => City::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
        ]);
    }

    public function store()
    {
        Organization::create(
            Request::validate([
                'name' => ['required', 'max:100'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable', 'max:2'],
                'postal_code' => ['nullable', 'max:25'],
                'customer_no' => ['required', 'numeric', 'unique:organizations'],
                'contact_person_1' => ['nullable', 'max:100'], // New field
                'contact_person_2' => ['nullable', 'max:100'], // New field
                'add_1' => ['nullable', 'max:150'], // New field
                'add_2' => ['nullable', 'max:150'], // New field
                'add_3' => ['nullable', 'max:150'], // New field
                'add_4' => ['nullable', 'max:150'], // New field
                'phone_1' => ['nullable', 'max:50'], // New field
                'phone_2' => ['nullable', 'max:50'], // New field
                'phone_3' => ['nullable', 'max:50'], // New field
                'fax_1' => ['nullable', 'max:50'], // New field
                'fax_2' => ['nullable', 'max:50'], // New field
                'mobile_1' => ['nullable', 'max:50'], // New field
                'mobile_2' => ['nullable', 'max:50'], // New field
                'web_site' => ['nullable', 'max:100'], // New field
            ])
        );

        return Redirect::route('organizations')->with('success', 'Customer created.');
    }

    public function edit(Organization $organization)
    {
        return Inertia::render('Organizations/Edit', [
            'title' => $organization->name,
            'countries' => Country::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'cities' => City::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'address' => $organization->address,
                'city' => $organization->city,
                'region' => $organization->region,
                'country' => $organization->country,
                'postal_code' => $organization->postal_code,
                'contacts' => $organization->contacts()->orderByName()->get()->map->only('id', 'name', 'city', 'phone'),
                'customer_no' => $organization->customer_no,
                'contact_person_1' => $organization->contact_person_1, // New field
                'contact_person_2' => $organization->contact_person_2, // New field
                'add_1' => $organization->add_1, // New field
                'add_2' => $organization->add_2, // New field
                'add_3' => $organization->add_3, // New field
                'add_4' => $organization->add_4, // New field
                'phone_1' => $organization->phone_1, // New field
                'phone_2' => $organization->phone_2, // New field
                'phone_3' => $organization->phone_3, // New field
                'fax_1' => $organization->fax_1, // New field
                'fax_2' => $organization->fax_2, // New field
                'mobile_1' => $organization->mobile_1, // New field
                'mobile_2' => $organization->mobile_2, // New field
                'web_site' => $organization->web_site, // New field
            ],
        ]);
    }

    public function update(Organization $organization)
    {
        $organization->update(
            Request::validate([
                'name' => ['required', 'max:100'],
                'email' => ['nullable'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable'],
                'postal_code' => ['nullable', 'max:25'],
                'customer_no' => [
                    'required',
                    'numeric',
                    'unique:organizations,customer_no,' . $organization->id
                ],
                'contact_person_1' => ['nullable', 'max:100'], // New field
                'contact_person_2' => ['nullable', 'max:100'], // New field
                'add_1' => ['nullable', 'max:150'], // New field
                'add_2' => ['nullable', 'max:150'], // New field
                'add_3' => ['nullable', 'max:150'], // New field
                'add_4' => ['nullable', 'max:150'], // New field
                'phone_1' => ['nullable', 'max:50'], // New field
                'phone_2' => ['nullable', 'max:50'], // New field
                'phone_3' => ['nullable', 'max:50'], // New field
                'fax_1' => ['nullable', 'max:50'], // New field
                'fax_2' => ['nullable', 'max:50'], // New field
                'mobile_1' => ['nullable', 'max:50'], // New field
                'mobile_2' => ['nullable', 'max:50'], // New field
                'web_site' => ['nullable', 'max:100'], // New field
            ])
        );

        return Redirect::back()->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return Redirect::route('organizations')->with('success', 'Organization deleted.');
    }

    public function restore(Organization $organization)
    {
        $organization->restore();

        return Redirect::back()->with('success', 'Organization restored.');
    }
}
