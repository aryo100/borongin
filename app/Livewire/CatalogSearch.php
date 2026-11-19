<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class CatalogSearch extends Component
{
    public string $query = '';

    public function mount(string $initial = ''): void
    {
        $this->query = mb_substr($initial, 0, 100);
    }

    public function render()
    {
        $term = trim(mb_substr($this->query, 0, 100));

        return view('livewire.catalog-search', [
            'suggestions' => mb_strlen($term) < 2 ? collect() : Product::query()
                ->where('active', true)->whereLike('name', '%'.$term.'%')->orderBy('name')
                ->limit(5)->get(['id', 'name', 'slug']),
        ]);
    }
}
