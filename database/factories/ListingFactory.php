<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    public function definition()
    {
        $locations = [

            // GUWAHATI

            [
                'city' => 'Guwahati',
                'locality' => 'Paltan Bazar',
                'pincode' => '781008',
                'latitude' => 26.1826,
                'longitude' => 91.7477,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Ganeshguri',
                'pincode' => '781006',
                'latitude' => 26.1465,
                'longitude' => 91.7904,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Khanapara',
                'pincode' => '781022',
                'latitude' => 26.1237,
                'longitude' => 91.8206,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Beltola',
                'pincode' => '781028',
                'latitude' => 26.1110,
                'longitude' => 91.8015,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Six Mile',
                'pincode' => '781022',
                'latitude' => 26.1336,
                'longitude' => 91.8058,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Dispur',
                'pincode' => '781006',
                'latitude' => 26.1433,
                'longitude' => 91.7898,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Zoo Road',
                'pincode' => '781024',
                'latitude' => 26.1664,
                'longitude' => 91.7845,
            ],

            [
                'city' => 'Guwahati',
                'locality' => 'Bamunimaidam',
                'pincode' => '781021',
                'latitude' => 26.1841,
                'longitude' => 91.7949,
            ],

            // DIBRUGARH

            [
                'city' => 'Dibrugarh',
                'locality' => 'Dibrugarh University',
                'pincode' => '786004',
                'latitude' => 27.4728,
                'longitude' => 94.9119,
            ],

            [
                'city' => 'Dibrugarh',
                'locality' => 'Milan Nagar',
                'pincode' => '786003',
                'latitude' => 27.4832,
                'longitude' => 94.9108,
            ],

            [
                'city' => 'Dibrugarh',
                'locality' => 'Chowkidinghee',
                'pincode' => '786001',
                'latitude' => 27.4720,
                'longitude' => 94.9050,
            ],

            [
                'city' => 'Dibrugarh',
                'locality' => 'Graham Bazar',
                'pincode' => '786001',
                'latitude' => 27.4862,
                'longitude' => 94.9041,
            ],

            [
    'city' => 'Dibrugarh',
    'locality' => 'Milan Nagar',
    'pincode' => '786003',
    'latitude' => 27.4832,
    'longitude' => 94.9108,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Dibrugarh University',
    'pincode' => '786004',
    'latitude' => 27.4728,
    'longitude' => 94.9119,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Chowkidinghee',
    'pincode' => '786001',
    'latitude' => 27.4720,
    'longitude' => 94.9050,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Graham Bazar',
    'pincode' => '786001',
    'latitude' => 27.4862,
    'longitude' => 94.9041,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Boiragimoth',
    'pincode' => '786003',
    'latitude' => 27.4898,
    'longitude' => 94.9125,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Convoy Road',
    'pincode' => '786001',
    'latitude' => 27.4795,
    'longitude' => 94.8994,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Amolapatty',
    'pincode' => '786001',
    'latitude' => 27.4830,
    'longitude' => 94.9028,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Thana Chariali',
    'pincode' => '786001',
    'latitude' => 27.4771,
    'longitude' => 94.9065,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Jhalukpara',
    'pincode' => '786001',
    'latitude' => 27.4710,
    'longitude' => 94.8940,
],

[
    'city' => 'Dibrugarh',
    'locality' => 'Assam Medical College',
    'pincode' => '786002',
    'latitude' => 27.4706,
    'longitude' => 94.9039,
],

//Dhemaji
[
    'city' => 'Dhemaji',
    'locality' => 'Dhemaji Town',
    'pincode' => '787057',
    'latitude' => 27.4833,
    'longitude' => 94.5860,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Silapathar',
    'pincode' => '787059',
    'latitude' => 27.5942,
    'longitude' => 94.7248,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Machkhowa',
    'pincode' => '787057',
    'latitude' => 27.5010,
    'longitude' => 94.5710,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Jonai',
    'pincode' => '787060',
    'latitude' => 27.7287,
    'longitude' => 95.2160,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Murkongselek',
    'pincode' => '787060',
    'latitude' => 27.7870,
    'longitude' => 95.3270,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Gogamukh',
    'pincode' => '787034',
    'latitude' => 27.5485,
    'longitude' => 94.9500,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Bordoloni',
    'pincode' => '787026',
    'latitude' => 27.3812,
    'longitude' => 95.0470,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Batgharia',
    'pincode' => '787057',
    'latitude' => 27.4880,
    'longitude' => 94.5920,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Sissiborgaon',
    'pincode' => '787110',
    'latitude' => 27.3610,
    'longitude' => 94.7210,
],

[
    'city' => 'Dhemaji',
    'locality' => 'Kulajan',
    'pincode' => '787057',
    'latitude' => 27.4705,
    'longitude' => 94.6010,
],

            // JORHAT

            [
                'city' => 'Jorhat',
                'locality' => 'AT Road',
                'pincode' => '785001',
                'latitude' => 26.7509,
                'longitude' => 94.2037,
            ],

            [
                'city' => 'Jorhat',
                'locality' => 'Tarajan',
                'pincode' => '785001',
                'latitude' => 26.7534,
                'longitude' => 94.2150,
            ],

            [
                'city' => 'Jorhat',
                'locality' => 'Choladhara',
                'pincode' => '785001',
                'latitude' => 26.7421,
                'longitude' => 94.2172,
            ],

            // TEZPUR

            [
                'city' => 'Tezpur',
                'locality' => 'Mission Chariali',
                'pincode' => '784001',
                'latitude' => 26.6524,
                'longitude' => 92.7926,
            ],

            [
                'city' => 'Tezpur',
                'locality' => 'Mahabhairab',
                'pincode' => '784001',
                'latitude' => 26.6316,
                'longitude' => 92.7924,
            ],

            // SILCHAR

            [
                'city' => 'Silchar',
                'locality' => 'Ambicapatty',
                'pincode' => '788004',
                'latitude' => 24.8255,
                'longitude' => 92.7979,
            ],

            [
                'city' => 'Silchar',
                'locality' => 'Tarapur',
                'pincode' => '788003',
                'latitude' => 24.8202,
                'longitude' => 92.7906,
            ],

            // NAGAON

            [
                'city' => 'Nagaon',
                'locality' => 'Haibargaon',
                'pincode' => '782002',
                'latitude' => 26.3530,
                'longitude' => 92.6920,
            ],

            [
                'city' => 'Nagaon',
                'locality' => 'Amoni',
                'pincode' => '782001',
                'latitude' => 26.3504,
                'longitude' => 92.6845,
            ],
        ];

        $location = $this->faker->randomElement($locations);

        $amenities = [
            'wifi',
            'parking',
            'lift',
            'power_backup',
            'security',
            'water_supply',
            'ac',
            'gym',
            'balcony',
            'cctv',
        ];

        return [

            'owner_id' => User::inRandomOrder()->first()->id,

'title' => $this->faker->randomElement([
    '2BHK Flat in ' . $location['locality'],
    'PG near ' . $location['locality'],
    'Family House in ' . $location['locality'],
    'Rental Room at ' . $location['locality'],
    'Fully Furnished Flat in ' . $location['locality'],
]),

            'description' => $this->faker->paragraphs(3, true),

            'property_type' => $this->faker->randomElement([
                'room',
                'shared_room',
                'flat',
                'house',
                'pg',
            ]),

            'bhk_type' => $this->faker->randomElement([
                '1rk',
                '1bhk',
                '2bhk',
                '3bhk',
                '4bhk',
                '5bhk',
            ]),

            'furnishing' => $this->faker->randomElement([
                'unfurnished',
                'semi_furnished',
                'fully_furnished',
            ]),

            'listing_class' => 'residential',

            'city' => $location['city'],

            'locality' => $location['locality'],

            'address' => $this->faker->randomElement([
                'Near Main Road',
                'Near Bus Stand',
                'Near Railway Station',
                'Near Market Area',
                'Near Hospital',
                'Near University',
            ]) . ', ' . $location['locality'],

            'pincode' => $location['pincode'],

            'latitude' => $location['latitude'],

            'longitude' => $location['longitude'],

            'rent_per_month' => $this->faker->numberBetween(4000, 50000),

            'deposit' => $this->faker->numberBetween(0, 100000),

            'area_sqft' => $this->faker->numberBetween(250, 3000),

            'floor_number' => $this->faker->numberBetween(0, 15),

            'gender_preference' => $this->faker->randomElement([
                'male',
                'female',
                'family',
                'any',
            ]),

            'amenities' => collect($amenities)
                ->random(rand(3, 7))
                ->values()
                ->toArray(),

            'photos' => [
                'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85',
                'https://images.unsplash.com/photo-1560185007-c5ca9d2c014d',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858',
            ],

            'status' => 'approved',

            'is_broker' => false,

            'is_featured' => rand(1, 10) <= 2,

            'view_count' => rand(10, 1000),

            'unlock_count' => rand(0, 100),
        ];
    }
}