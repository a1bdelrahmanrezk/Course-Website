<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            'Beginner',
            'Intermediate',
            'Advanced',
        ];

        foreach($levels as $level){
            Level::create([
                'name' => $level,
                'slug' => strtolower(str_replace(' ', '-', $level)),
            ]); 
        }
    }
}
