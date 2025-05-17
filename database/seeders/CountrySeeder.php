<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['country_code' => 'VN', 'country_name' => 'Vietnam'],
            ['country_code' => 'US', 'country_name' => 'United States'],
            ['country_code' => 'UK', 'country_name' => 'United Kingdom'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
