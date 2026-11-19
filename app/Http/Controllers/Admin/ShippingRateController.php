<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShippingRateController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->value();
        $rates = ShippingRate::query()
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($query): void {
                $builder->where('name', 'like', "%{$query}%")->orWhere('region', 'like', "%{$query}%");
            }))
            ->orderBy('region')->orderBy('price')->paginate(20)->withQueryString();

        return view('admin.rates.index', compact('rates', 'query'));
    }

    public function create(): View
    {
        return view('admin.rates.form', ['rate' => new ShippingRate(['active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ShippingRate::query()->create($this->validated($request, new ShippingRate));

        return to_route('admin.shipping-rates.index')->with('status', 'Tarif pengiriman dibuat.');
    }

    public function edit(ShippingRate $shippingRate): View
    {
        return view('admin.rates.form', ['rate' => $shippingRate]);
    }

    public function update(Request $request, ShippingRate $shippingRate): RedirectResponse
    {
        $shippingRate->update($this->validated($request, $shippingRate));

        return to_route('admin.shipping-rates.index')->with('status', 'Tarif pengiriman diperbarui.');
    }

    public function destroy(ShippingRate $shippingRate): RedirectResponse
    {
        $shippingRate->delete();

        return to_route('admin.shipping-rates.index')->with('status', 'Tarif pengiriman dihapus.');
    }

    private function validated(Request $request, ShippingRate $rate): array
    {
        $pickup = $request->boolean('pickup');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('shipping_rates')->ignore($rate)],
            'region' => ['nullable', Rule::requiredIf(! $pickup), 'string', 'max:120'],
            'price' => ['nullable', Rule::requiredIf(! $pickup), 'integer', 'min:0', 'max:100000000'],
            'pickup' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $data['pickup'] = $pickup;
        $data['active'] = $request->boolean('active');
        $data['price'] = $data['pickup'] ? 0 : $data['price'];
        $data['region'] = $pickup ? null : $data['region'];

        return $data;
    }
}
