<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->value();
        $kind = $request->string('kind')->value();
        $status = $request->string('status')->value();

        $tickets = Ticket::query()->with('order')
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($query): void {
                $builder->where('subject', 'like', "%{$query}%")
                    ->orWhereHas('order', fn ($orders) => $orders->where('public_id', 'like', "%{$query}%"));
            }))
            ->when(in_array($kind, ['support', 'return'], true), fn ($builder) => $builder->where('kind', $kind))
            ->when(in_array($status, ['open', 'answered', 'closed'], true), fn ($builder) => $builder->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.tickets.index', compact('tickets', 'query', 'kind', 'status'));
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load('order');

        return view('admin.tickets.show', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'answered', 'closed'])],
            'response' => ['nullable', 'string', 'max:5000', 'required_unless:status,open'],
        ]);

        $ticket->update(['status' => $data['status'], 'response' => $data['response'] ?? null]);

        return to_route('admin.tickets.show', $ticket)->with('status', 'Tanggapan dan status tiket disimpan.');
    }
}
