<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            [
                'name' => 'Nasi Goreng Spesial',
                'price' => 25000,
                'description' => 'Nasi goreng dengan telur, ayam suwir, dan udang.',
                'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&q=80&w=300',
                'qty' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mie Goreng Seafood',
                'price' => 28000,
                'description' => 'Mie goreng dengan campuran cumi dan udang segar.',
                'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&q=80&w=300',
                'qty' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ayam Bakar Madu',
                'price' => 30000,
                'description' => 'Ayam bakar dengan lumuran madu manis gurih.',
                'image' => 'https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?auto=format&fit=crop&q=80&w=300',
                'qty' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sate Ayam Madura',
                'price' => 22000,
                'description' => '10 tusuk sate ayam dengan bumbu kacang kental.',
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&q=80&w=300',
                'qty' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Soto Ayam Lamongan',
                'price' => 20000,
                'description' => 'Soto kuah kuning dengan koya gurih dan ayam kampung.',
                'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&q=80&w=300',
                'qty' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gado-Gado',
                'price' => 18000,
                'description' => 'Sayuran rebus dengan bumbu kacang dan kerupuk.',
                'image' => 'https://images.unsplash.com/photo-1504544750208-dc0358e63f7f?auto=format&fit=crop&q=80&w=300',
                'qty' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rendang Daging Sapi',
                'price' => 35000,
                'description' => 'Daging sapi empuk dimasak dengan rempah khas Minang.',
                'image' => 'https://images.unsplash.com/photo-1574484284002-952d92456975?auto=format&fit=crop&q=80&w=300',
                'qty' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bakso Kuah Mercon',
                'price' => 15000,
                'description' => 'Bakso sapi dengan kuah cabai ekstra pedas.',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&q=80&w=300',
                'qty' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nasi Padang Komplit',
                'price' => 40000,
                'description' => 'Nasi dengan rendang, ayam pop, sayur nangka, dan sambal ijo.',
                'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&q=80&w=300',
                'qty' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Es Campur Spesial',
                'price' => 12000,
                'description' => 'Es serut dengan aneka buah, cincau, dan susu kental manis.',
                'image' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&q=80&w=300',
                'qty' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($foods as $food) {
            DB::table('foods')->updateOrInsert(
                ['name' => $food['name']],
                $food
            );
        }
    }
}
