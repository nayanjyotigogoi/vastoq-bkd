<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Furniture;

class FurnitureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $items = [

            [
                'name' => 'Godrej Single Bed',
                'category' => 'bed',
                'description' => 'Comfortable single bed suitable for students and professionals.',
                'price_per_month' => 499,
                'image_url' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85',
                'is_available' => true,
            ],

            [
                'name' => 'Queen Size Bed',
                'category' => 'bed',
                'description' => 'Spacious queen size bed with sturdy frame.',
                'price_per_month' => 799,
                'image_url' => 'https://images.unsplash.com/photo-1505693536294-233fb141754c',
                'is_available' => true,
            ],

            [
                'name' => 'Orthopedic Mattress',
                'category' => 'mattress',
                'description' => 'Premium orthopedic mattress for better sleep.',
                'price_per_month' => 299,
                'image_url' => 'https://images.unsplash.com/photo-1540518614846-7eded433c457',
                'is_available' => true,
            ],

            [
                'name' => 'L Shape Sofa',
                'category' => 'sofa',
                'description' => 'Modern L-shaped sofa for living rooms.',
                'price_per_month' => 999,
                'image_url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc',
                'is_available' => true,
            ],

            [
                'name' => '3 Seater Sofa',
                'category' => 'sofa',
                'description' => 'Comfortable three seater sofa.',
                'price_per_month' => 699,
                'image_url' => 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e',
                'is_available' => true,
            ],

            [
                'name' => 'Dining Table Set',
                'category' => 'dining_table',
                'description' => '4-seater dining table set.',
                'price_per_month' => 799,
                'image_url' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85',
                'is_available' => true,
            ],

            [
                'name' => 'Office Chair',
                'category' => 'chair',
                'description' => 'Ergonomic office chair.',
                'price_per_month' => 199,
                'image_url' => 'https://images.unsplash.com/photo-1505843513577-22bb7d21e455',
                'is_available' => true,
            ],

            [
                'name' => 'Study Table',
                'category' => 'study_table',
                'description' => 'Compact study table for students.',
                'price_per_month' => 249,
                'image_url' => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd',
                'is_available' => true,
            ],

            [
                'name' => 'Wooden Wardrobe',
                'category' => 'wardrobe',
                'description' => 'Two-door wooden wardrobe.',
                'price_per_month' => 499,
                'image_url' => 'https://images.unsplash.com/photo-1484101403633-562f891dc89a',
                'is_available' => true,
            ],

            [
                'name' => 'Samsung Refrigerator',
                'category' => 'refrigerator',
                'description' => 'Double-door refrigerator.',
                'price_per_month' => 699,
                'image_url' => 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30',
                'is_available' => true,
            ],

            [
                'name' => 'LG Washing Machine',
                'category' => 'washing_machine',
                'description' => 'Fully automatic washing machine.',
                'price_per_month' => 649,
                'image_url' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1',
                'is_available' => true,
            ],

            [
                'name' => 'Voltas Air Conditioner',
                'category' => 'air_conditioner',
                'description' => '1.5 Ton inverter AC.',
                'price_per_month' => 1199,
                'image_url' => 'https://images.unsplash.com/photo-1581093458791-9f3c3900df4b',
                'is_available' => true,
            ],

            [
                'name' => 'Samsung Smart TV',
                'category' => 'television',
                'description' => '43 inch Smart LED TV.',
                'price_per_month' => 799,
                'image_url' => 'https://images.unsplash.com/photo-1593784991095-a205069470b6',
                'is_available' => true,
            ],
        ];

        foreach ($items as $item) {
            Furniture::create($item);
        }
    }
}