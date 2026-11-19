<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', 'pending')->value();
        $query = $request->string('q')->trim()->value();
        $memberships = Membership::query()->with('user')
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($query): void {
                $builder->where('school_name', 'like', "%{$query}%")
                    ->orWhereHas('user', fn ($users) => $users->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"));
            }))
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($builder) => $builder->where('status', $status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.memberships.index', compact('memberships', 'status', 'query'));
    }

    public function update(Request $request, Membership $membership): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'note' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected'],
        ]);

        if (! Membership::query()->whereKey($membership->id)->where('status', 'pending')->update([
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
        ])) {
            return back()->withErrors(['membership' => 'Pengajuan ini sudah ditinjau.']);
        }

        return back()->with('status', 'Pengajuan anggota diperbarui.');
    }
}
