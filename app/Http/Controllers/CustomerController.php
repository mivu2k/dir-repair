<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->withCount('repairJobs')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function show(Customer $customer)
    {
        $customer->loadCount('repairJobs');
        
        $jobs = $customer->repairJobs()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'organization' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'communication_preference' => 'required|in:phone,email,whatsapp',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        if ($request->wantsJson()) {
            return response()->json($customer);
        }

        return redirect()->route('customers.show', $customer)->with('message', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'organization' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'communication_preference' => 'required|in:phone,email,whatsapp',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)->with('message', 'Customer updated successfully.');
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->checkDeletePermission($request);

        if ($customer->repairJobs()->exists()) {
            return back()->withErrors(['message' => 'Cannot delete customer with existing jobs.']);
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('message', 'Customer deleted successfully.');
    }
}
