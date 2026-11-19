<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'banner' => Setting::valueFor('banner', []),
            'faqs' => Setting::valueFor('faqs', []),
            'contact' => Setting::valueFor('contact', []),
            'bank' => Setting::valueFor('bank', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'banner' => ['required', 'array:title,description,cta,active,remove_image'],
            'banner.title' => ['nullable', 'string', 'max:160'],
            'banner.description' => ['nullable', 'string', 'max:1000'],
            'banner.cta' => ['nullable', 'string', 'max:80'],
            'banner.active' => ['required', 'boolean'],
            'banner.remove_image' => ['sometimes', 'boolean'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'faqs' => ['sometimes', 'array', 'max:30'],
            'faqs.*' => ['array:question,answer'],
            'faqs.*.question' => ['required_with:faqs.*.answer', 'nullable', 'string', 'max:200'],
            'faqs.*.answer' => ['required_with:faqs.*.question', 'nullable', 'string', 'max:3000'],
            'contact' => ['required', 'array:email,phone,whatsapp,address'],
            'contact.email' => ['nullable', 'email', 'max:150'],
            'contact.phone' => ['nullable', 'string', 'max:40'],
            'contact.whatsapp' => ['nullable', 'string', 'regex:/^\+?[0-9 ()\-]{6,30}$/'],
            'contact.address' => ['nullable', 'string', 'max:500'],
            'bank' => ['required', 'array:name,number,holder'],
            'bank.name' => ['nullable', 'required_with:bank.number,bank.holder', 'string', 'max:100'],
            'bank.number' => ['nullable', 'required_with:bank.name,bank.holder', 'regex:/^[0-9 .\-]{4,40}$/'],
            'bank.holder' => ['nullable', 'required_with:bank.name,bank.number', 'string', 'max:150'],
        ]);

        $newPath = $request->file('banner_image')?->store('banners', 'public');
        abort_if($newPath === false, 503, 'Gambar banner tidak dapat disimpan.');

        try {
            $oldPath = DB::transaction(function () use ($request, $data, $newPath): ?string {
                $previous = Setting::query()->where('key', 'banner')->lockForUpdate()->first()?->value ?? [];
                $oldPath = $previous['image_path'] ?? null;
                $banner = array_replace($previous, $data['banner'], [
                    'active' => $request->boolean('banner.active'),
                    'image_path' => $newPath ?? ($request->boolean('banner.remove_image') ? null : $oldPath),
                ]);
                unset($banner['remove_image']);

                $faqs = array_values(array_filter($data['faqs'] ?? [], fn (array $faq) => filled($faq['question'] ?? null)));
                $settings = [
                    'banner' => $banner,
                    'faqs' => $faqs,
                    'contact' => $data['contact'],
                    'bank' => $data['bank'],
                ];

                foreach ($settings as $key => $value) {
                    Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
                }

                return $banner['image_path'] !== $oldPath ? $oldPath : null;
            }, 3);
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath && str_starts_with($oldPath, 'banners/')) {
            Storage::disk('public')->delete($oldPath);
        }

        return to_route('admin.settings.edit')->with('status', 'Pengaturan toko disimpan.');
    }
}
