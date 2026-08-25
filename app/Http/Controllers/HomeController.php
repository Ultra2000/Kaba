<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'featured' => Listing::with(['user:id,name', 'category:id,name,slug', 'photos'])
                ->where('status', 'active')
                ->where('type', '!=', 'recherche')
                ->orderByDesc('views')
                ->take(10)
                ->get(),
            'categories' => Category::browsable()->orderBy('name')->get(['id', 'name', 'slug', 'icon', 'image']),
            'topRomans' => Listing::with(['photos', 'category:id,slug'])
                ->whereHas('category', fn ($q) => $q->where('slug', 'roman'))
                ->where('status', 'active')
                ->where('type', '!=', 'recherche')
                ->orderByDesc('rating')
                ->orderByDesc('views')
                ->take(4)
                ->get(),
            'shelves' => $this->shelves(),
        ]);
    }

    /** Étagères par catégorie (label + livres) pour la disposition « bibliothèque ». */
    private function shelves(): array
    {
        // slug => description courte
        $wanted = [
            'roman'     => 'Plongez dans des histoires captivantes.',
            'dev-perso' => 'Des livres pour grandir et s’épanouir.',
            'scolaire'  => 'Manuels et annales pour réussir.',
            'histoire'  => 'Comprendre le monde qui nous entoure.',
        ];

        $categories = Category::whereIn('slug', array_keys($wanted))->get()->keyBy('slug');
        $shelves = [];

        foreach ($wanted as $slug => $description) {
            $cat = $categories->get($slug);
            if (! $cat) {
                continue;
            }
            $books = Listing::with('photos')
                ->where('category_id', $cat->id)
                ->where('status', 'active')
                ->where('type', '!=', 'recherche')
                ->orderByDesc('views')
                ->take(12)
                ->get();

            if ($books->isEmpty()) {
                continue;
            }

            $shelves[] = [
                'title'       => $cat->name,
                'slug'        => $cat->slug,
                'icon'        => $cat->icon,
                'description' => $description,
                'books'       => $books,
            ];
        }

        return $shelves;
    }
}
