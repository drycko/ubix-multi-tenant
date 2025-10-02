<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomAmenitySeeder extends Seeder
{
  /**
  * Run the database seeds.
  */
  public function run(): void
  {
    // Seed some default room amenities with valid bootstrap icons
    $amenities = [
      ['name' => 'Ensuite Bathroom', 'icon' => 'bi bi-door-open', 'description' => 'Private bathroom attached to the room.'],
      ['name' => 'Television', 'icon' => 'bi bi-tv', 'description' => 'Flat-screen TV with cable/satellite channels.'],
      ['name' => 'DSTV', 'icon' => 'bi bi-broadcast', 'description' => 'Access to DSTV channels.'],
      ['name' => 'Fireplace', 'icon' => 'bi bi-fire', 'description' => 'Cozy fireplace for warmth and ambiance.'],
      ['name' => 'Air Conditioning', 'icon' => 'bi bi-snow', 'description' => 'Climate control for comfort.'],
      ['name' => 'Balcony', 'icon' => 'bi bi-building', 'description' => 'Private balcony with outdoor seating.'],
      ['name' => 'Spa Bath', 'icon' => 'bi bi-droplet', 'description' => 'Relaxing spa bath in the room.'],
      ['name' => 'Shower', 'icon' => 'bi bi-droplet-half', 'description' => 'Modern shower facilities.'],
      ['name' => 'Tea/Coffee Facilities', 'icon' => 'bi bi-cup-hot', 'description' => 'In-room tea and coffee making facilities.'],
      ['name' => 'Forest View', 'icon' => 'bi bi-tree', 'description' => 'Scenic views of the surrounding forest.'],
      ['name' => 'Underfloor Heating', 'icon' => 'bi bi-thermometer-half', 'description' => 'Warm floors for added comfort.'],
      // add more amenities missing from RoomTypeSeeder if needed
      ['name' => 'Basic Furniture', 'icon' => 'bi bi-house-door', 'description' => 'Essential furniture including bed, wardrobe, and desk.'],
      ['name' => 'Bath and Shower', 'icon' => 'bi bi-droplet-fill', 'description' => 'Combination of bath and shower facilities.'],
      ['name' => 'Garden View', 'icon' => 'bi bi-flower1', 'description' => 'Rooms with views of the garden area.'],
      ['name' => 'Lounge Area', 'icon' => 'bi bi-house-heart', 'description' => 'Separate lounge area within the room.'],
      ['name' => 'Private Courtyard', 'icon' => 'bi bi-house', 'description' => 'Exclusive courtyard space for the room.'],

    ];
    // Great, I will like to add a icon selector in the future when I work on the amenity management feature
    // Get all properties
    $property_ids = \App\Models\Tenant\Property::pluck('id')->toArray();
    
    // Insert base amenities for each property (allows per-property customization)
    // This approach ensures each property can manage their own amenities independently
    foreach ($property_ids as $propertyId) {
      foreach ($amenities as $amenity) {
        $slug = \Str::slug($amenity['name']);
        $slug = str_replace('-', '_', $slug); // replace hyphens with underscores in slug
        
        // Check if amenity already exists for this property to avoid duplicates
        $exists = \DB::table('room_amenities')
          ->where('property_id', $propertyId)
          ->where('slug', $slug)
          ->exists();
          
        if (!$exists) {
          \DB::table('room_amenities')->insert([
            'property_id' => $propertyId,
            'name' => $amenity['name'],
            'slug' => $slug,
            'icon' => $amenity['icon'],
            'description' => $amenity['description'],
            'created_at' => now(),
            'updated_at' => now(),
          ]);
        }
      }
    }
    // run this command to seed tenant (tenancy) RoomAmenitySeeder to database: sail artisan tenancy:seed --class=RoomAmenitySeeder
    // rollback the migration: sail artisan migrate:rollback
  }
}
