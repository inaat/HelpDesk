<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class FilterController extends Controller {
    //
    public function customers()
    {
        $customerRole = Role::where('slug', 'customer')->first();
    
        // Get the search query from the request
        $searchQuery = Request::input('search');
    
        // Fetch customers
    // Fetch customers
$customers = User::with('organization')
->where('role_id', $customerRole ? $customerRole->id : 0)
->when($searchQuery, function ($query, $searchQuery) {
    $query->where(function ($q) use ($searchQuery) {
        $q->whereHas('organization', function ($orgQuery) use ($searchQuery) {
            // Search by organization customer number or name
            $orgQuery->where('customer_no', 'LIKE', '%' . $searchQuery . '%')
                     ->orWhere('name', 'LIKE', '%' . $searchQuery . '%')
                     ->orWhere('name_en', 'LIKE', '%' . $searchQuery . '%');
        });
    })
    ->orWhere('users.first_name', 'LIKE', '%' . $searchQuery . '%') // Also search by user name
    ->orWhere('users.phone', 'LIKE', '%' . $searchQuery . '%'); // And phone number
})
->Join('organizations', 'users.organization_id', '=', 'organizations.id') // Join organizations table
->orderBy('organizations.customer_no') // Order by customer_no from the organizations table
->select('users.*') // Select only user attributes to avoid conflicts
->limit(30)
->get()
->map(function ($user) {
    return [
        'id' => $user->id,
        // Concatenate organization customer number, organization name, user name, and phone number
        'name' => ($user->organization 
            ? ' (' . $user->organization->customer_no . ' ' . $user->organization->name . ')' 
            : '') . 
            $user->name . ' ' . $user->phone,
    ];
});

        return response()->json($customers);
    }
    

    public function assignees(){

        $search = Request::input('search');
        $assignees = [];
        if(!empty($search)){
            $ticketAssignees = Ticket::whereHas('assignedTo', function($q) use ($search){
                $q->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%');
            })->with('assignedTo:id,first_name,last_name')->select('assigned_to')->groupBy('assigned_to')->limit(5)->get();
        }else{
            $ticketAssignees = Ticket::whereHas('assignedTo')->with('assignedTo:id,first_name,last_name')->select('assigned_to')->groupBy('assigned_to')->limit(5)->get();
        }

        foreach ($ticketAssignees as $ticketAssignee){
            $assignees[] = ['id' => $ticketAssignee->assignedTo['id'], 'name' => $ticketAssignee->assignedTo['first_name'].' '.$ticketAssignee->assignedTo['last_name']];
        }

        return response()->json($assignees);
    }

    public function usersExceptCustomer(){
        $customerRole = Role::where('slug', 'customer')->first();
        $customers = User::where('role_id', '!=', $customerRole ? $customerRole->id : 0)
            ->filter(Request::only('search'))
            ->limit(6)
            ->get()
            ->map
            ->only('id', 'name');
        return response()->json($customers);
    }
    public function getOrganization()
    {
        // Check if organization_id is provided
        if (request()->has('organization_id')) { // Use request() helper
            // Fetch the specific organization by organization_id
            $organization = Organization::find(request()->organization_id);
    
            if ($organization) {
                return response()->json($organization);
            }
    
            return response()->json(['message' => 'Organization not found.'], 404);
        }
    
        // If organization_id is not provided, return a message
        return response()->json(['message' => 'No organization ID provided.'], 400);
    }}
