<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributeValues = [
            ['attribute_id' => 3, 'value' => 'ASH'],
            ['attribute_id' => 3, 'value' => 'AZALEA'],
            ['attribute_id' => 3, 'value' => 'BLACK'],
            ['attribute_id' => 3, 'value' => 'BLACKBERRY'],
            ['attribute_id' => 3, 'value' => 'CARDINAL RED'],
            ['attribute_id' => 3, 'value' => 'CAROLINA BLUE'],
            ['attribute_id' => 3, 'value' => 'CHARCOAL'],
            ['attribute_id' => 3, 'value' => 'COBALT'],
            ['attribute_id' => 3, 'value' => 'CORNSILK'],
            ['attribute_id' => 3, 'value' => 'DAISY'],
            ['attribute_id' => 3, 'value' => 'DARK CHOCOLATE'],
            ['attribute_id' => 3, 'value' => 'DARK HEATHER'],
            ['attribute_id' => 3, 'value' => 'FOREST'],
            ['attribute_id' => 3, 'value' => 'GOLD'],
            ['attribute_id' => 3, 'value' => 'GRAPHITE HEATHER'],
            ['attribute_id' => 3, 'value' => 'HEATHER SAPPHIRE'],
            ['attribute_id' => 3, 'value' => 'HELICONIA'],
            ['attribute_id' => 3, 'value' => 'INDIGO BLUE'],
            ['attribute_id' => 3, 'value' => 'IRISH GREEN'],
            ['attribute_id' => 3, 'value' => 'KIWI'],
            ['attribute_id' => 3, 'value' => 'LIGHT BLUE'],
            ['attribute_id' => 3, 'value' => 'LIGHT PINK'],
            ['attribute_id' => 3, 'value' => 'LILAC'],
            ['attribute_id' => 3, 'value' => 'LIME'],
            ['attribute_id' => 3, 'value' => 'MAROON'],
            ['attribute_id' => 3, 'value' => 'MIDNIGHT'],
            ['attribute_id' => 3, 'value' => 'MILITARY GREEN'],
            ['attribute_id' => 3, 'value' => 'MINT'],
            ['attribute_id' => 3, 'value' => 'NATURAL'],
            ['attribute_id' => 3, 'value' => 'NAVY'],
            ['attribute_id' => 3, 'value' => 'OLD GOLD'],
            ['attribute_id' => 3, 'value' => 'ORANGE'],
            ['attribute_id' => 3, 'value' => 'PURPLE'],
            ['attribute_id' => 3, 'value' => 'RED'],
            ['attribute_id' => 3, 'value' => 'ROYAL'],
            ['attribute_id' => 3, 'value' => 'SAFETY GREEN'],
            ['attribute_id' => 3, 'value' => 'SAND'],
            ['attribute_id' => 3, 'value' => 'SAPPHIRE'],
            ['attribute_id' => 3, 'value' => 'SKY'],
            ['attribute_id' => 3, 'value' => 'SPORTS GREY'],
            ['attribute_id' => 3, 'value' => 'SUNSET'],
            ['attribute_id' => 3, 'value' => 'TANGERINE'],
            ['attribute_id' => 3, 'value' => 'TWEED'],
            ['attribute_id' => 3, 'value' => 'VIOLET'],
            ['attribute_id' => 3, 'value' => 'WHITE'],
            ['attribute_id' => 3, 'value' => 'YELLOW HAZE'],
            ['attribute_id' => 4, 'value' => 'S'],
            ['attribute_id' => 4, 'value' => 'M'],
            ['attribute_id' => 4, 'value' => 'L'],
            ['attribute_id' => 4, 'value' => 'XL'],
            ['attribute_id' => 4, 'value' => '2XL'],
            ['attribute_id' => 4, 'value' => '3XL'],
            ['attribute_id' => 4, 'value' => '4XL'],
            ['attribute_id' => 4, 'value' => '5XL'],
            ['attribute_id' => 5, 'value' => 'Front'],
            ['attribute_id' => 5, 'value' => 'Back'],
            ['attribute_id' => 5, 'value' => 'Left sleeve'],
            ['attribute_id' => 5, 'value' => 'Right sleeve'],
            ['attribute_id' => 5, 'value' => 'Hem'],
        ];

        foreach ($attributeValues as $attributeValue) {
            AttributeValue::create($attributeValue);
        }
    }
}
