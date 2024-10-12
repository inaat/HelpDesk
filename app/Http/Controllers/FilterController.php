<?php

namespace App\Http\Controllers;

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
        $customers = User::with('organization')
            ->where('role_id', $customerRole ? $customerRole->id : 0)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->whereHas('organization', function ($orgQuery) use ($searchQuery) {
                        // Search by organization customer number or name
                        $orgQuery->where('customer_no', 'LIKE', '%' . $searchQuery . '%')
                                 ->orWhere('name', 'LIKE', '%' . $searchQuery . '%');
                    });
                });
              //  ->orWhere('name', 'LIKE', '%' . $searchQuery . '%') // Also search by user name
               // ->orWhere('phone', 'LIKE', '%' . $searchQuery . '%'); // And phone number
            })
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
}
