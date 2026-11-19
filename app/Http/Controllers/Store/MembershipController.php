<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MembershipController
{
    public function show(Request $request): Response
    {
        return response()->view('store.membership', ['membership' => $request->user()?->membership], headers: ['Cache-Control' => 'private, no-store']);
    }

    public function apply(Request $request): RedirectResponse
    {
        $data = $request->validate(['school_name' => ['required', 'string', 'max:150']]);

        DB::transaction(function () use ($request, $data): void {
            $relation = $request->user()->membership();
            $application = $relation->firstOrCreate(['user_id' => $request->user()->id], $data + ['status' => 'pending']);

            if ($application->wasRecentlyCreated) {
                return;
            }

            $application = $relation->lockForUpdate()->firstOrFail();
            if ($application->status !== 'rejected') {
                throw ValidationException::withMessages(['school_name' => 'Pengajuan keanggotaan sudah tercatat.']);
            }

            $application->update($data + ['status' => 'pending', 'note' => null]);
        }, attempts: 5);

        return redirect()->route('store.membership')->with('status', 'Pengajuan keanggotaan berhasil dikirim.');
    }
}
