<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display listing of customers (pembeli/pelanggan)
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $customers = Customer::withCount('sales')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    /**
     * Show form to create a new customer
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store new customer in database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan / pembeli wajib diisi.',
            'email.email' => 'Format email pelanggan tidak valid.',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan / pembeli berhasil ditambahkan.');
    }

    /**
     * Show form to edit customer
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer details
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan / pembeli wajib diisi.',
            'email.email' => 'Format email pelanggan tidak valid.',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan / pembeli berhasil diperbarui.');
    }

    /**
     * Delete customer
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena tercatat dalam ' . $customer->sales()->count() . ' riwayat transaksi penjualan.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
