<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\RoomType;
use App\Models\Tenant\Property;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
  public function run(): void
  {
    // Get the first tenant property
    $property = Property::first();
    
    $roomTypes = [
      [
        'property_id' => $property->id,
        'legacy_code' => 'ST',
        'name' => 'Standard Room',
        'code' => 'STD',
        'description' => 'Comfortable standard rooms with essential amenities',
        'base_capacity' => 2,
        'max_capacity' => 2,
        'amenities' => json_encode(['ensuite_bathroom', 'basic_furniture']),
        'is_active' => true
      ],
      [
        'property_id' => $property->id,
        'legacy_code' => 'STV',
        'name' => 'Standard Room with TV',
        'code' => 'STD-TV',
        'description' => 'Standard rooms equipped with television',
        'base_capacity' => 2,
        'max_capacity' => 2,
        'amenities' => json_encode(['ensuite_bathroom', 'television', 'basic_furniture']),
        'is_active' => true
      ],
      [
        'property_id' => $property->id,
        'legacy_code' => 'GR',
        'name' => 'Garden Suite',
        'code' => 'GRD',
        'description' => 'Larger rooms with garden views and additional amenities',
        'base_capacity' => 2,
        'max_capacity' => 2,
        'amenities' => json_encode(['ensuite_bathroom', 'television', 'tea_facilities', 'bath_and_shower', 'garden_view']),
        'is_active' => true
      ],
      [
        'property_id' => $property->id,
        'legacy_code' => 'FN',
        'name' => 'Fountain Suite',
        'code' => 'FNT',
        'description' => 'Spacious suites with premium amenities',
        'base_capacity' => 2,
        'max_capacity' => 2,
        'amenities' => json_encode(['ensuite_bathroom', 'television', 'fireplace', 'aircon', 'lounge_area', 'tea_facilities', 'private_courtyard', 'bath_and_shower']),
        'is_active' => true
      ],
      [
        'property_id' => $property->id,
        'legacy_code' => 'FR',
        'name' => 'Forest Suite',
        'code' => 'FRST',
        'description' => 'Luxurious suites with forest views and premium features',
        'base_capacity' => 2,
        'max_capacity' => 2,
        'amenities' => json_encode(['ensuite_bathroom', 'television', 'dstv', 'fireplace', 'aircon', 'balcony', 'spa_bath', 'shower', 'tea_facilities', 'forest_view', 'underfloor_heating']),
        'is_active' => true
      ],
      [
        'property_id' => $property->id,
        'legacy_code' => 'CY',
        'name' => 'Courtyard Suite',
        'code' => 'CTYD',
        'description' => 'Premium suites with courtyard access and luxury amenities',
        'base_capacity' => 4,
        'max_capacity' => 4,
        'amenities' => json_encode(['ensuite_bathroom', 'television', 'dstv', 'fireplace', 'underfloor_heating', 'ceiling_fan', 'tea_facilities', 'separate_lounge', 'separate_toilet', 'courtyard_access']),
        'is_active' => true
        ]
      ];
      
      foreach ($roomTypes as $roomTypeData) {
        // create if not exists
        if (RoomType::where('code', $roomTypeData['code'])->where('property_id', $property->id)->exists()) {
          continue;
        }
        RoomType::create($roomTypeData);
      }
      
      $this->command->info('Room types seeded successfully!');
    }
  }