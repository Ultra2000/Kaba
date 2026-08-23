<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Romans', 'roman', 'fa-feather-pointed'],
            ['Mangas', 'manga', 'fa-dragon'],
            ['Bandes dessinées', 'bd', 'fa-wand-magic-sparkles'],
            ['Scolaires', 'scolaire', 'fa-school'],
            ['Universitaires', 'universitaire', 'fa-graduation-cap'],
            ['Développement personnel', 'dev-perso', 'fa-seedling'],
            ['Économie', 'economie', 'fa-chart-line'],
            ['Informatique', 'informatique', 'fa-laptop-code'],
            ['Sciences', 'sciences', 'fa-flask'],
            ['Droit', 'droit', 'fa-scale-balanced'],
            ['Santé', 'sante', 'fa-heart-pulse'],
            ['Religion', 'religion', 'fa-place-of-worship'],
            ['Histoire', 'histoire', 'fa-landmark'],
            ['Jeunesse', 'jeunesse', 'fa-child-reaching'],
            ['Cuisine', 'cuisine', 'fa-utensils'],
            ['Langues', 'langues', 'fa-language'],
            ['Concours', 'concours', 'fa-trophy'],
            ['Magazines', 'magazine', 'fa-newspaper'],
        ];

        foreach ($categories as [$name, $slug, $icon]) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name, 'icon' => $icon]);
        }
    }
}
