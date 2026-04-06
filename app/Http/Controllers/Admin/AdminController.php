<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $admins = User::query()
            ->where('business_id', Auth::user()->business_id)
            ->where('role', 'admin')
            ->latest()
            ->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['business_id'] = Auth::user()->business_id;
        $data['role'] = 'admin';
        $data['is_active'] = (bool) $data['is_active'];

        User::create($data);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    public function edit(User $admin): View
    {
        $admin = $this->resolveAdminFromCurrentBusiness($admin);

        return view('admin.admins.edit', compact('admin'));
    }

    public function update(UpdateAdminRequest $request, User $admin): RedirectResponse
    {
        $admin = $this->resolveAdminFromCurrentBusiness($admin);
        $data = $request->validated();
        $data['is_active'] = (bool) $data['is_active'];

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($admin->is(Auth::user()) && ! $data['is_active']) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $admin->update($data);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        $admin = $this->resolveAdminFromCurrentBusiness($admin);

        if ($admin->is(Auth::user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    private function resolveAdminFromCurrentBusiness(User $admin): User
    {
        abort_unless(
            $admin->role === 'admin' && $admin->business_id === Auth::user()->business_id,
            404
        );

        return $admin;
    }
}
