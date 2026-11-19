@auth
    <nav class="account-nav" aria-label="Akun saya">
        <a href="{{ route('store.orders.index') }}" @if(request()->routeIs('store.orders.index')) aria-current="page" @endif>@include('store.partials.icon', ['name' => 'receipt']) Pesanan</a>
        <a href="{{ route('store.membership') }}" @if(request()->routeIs('store.membership')) aria-current="page" @endif>@include('store.partials.icon', ['name' => 'shield']) Member Sekolah</a>
        @if(Route::has('profile.edit'))<a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.edit')) aria-current="page" @endif>@include('store.partials.icon', ['name' => 'user']) Profil</a>@endif
        @if(Route::has('logout'))<form method="POST" action="{{ route('logout') }}">@csrf<button class="plain-link" type="submit">Keluar</button></form>@endif
    </nav>
@endauth
