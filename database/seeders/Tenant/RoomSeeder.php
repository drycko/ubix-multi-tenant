<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Property;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
  public function run(): void
  {
    // Get the first tenant property
    $property = Property::first();
    if (!$property) {
      $this->command->error('No properties found. Please create a property first.');
      return;
    }
    
    // seed room types if none exist
    $this->call(RoomTypeSeeder::class);
    
    // Map legacy room types to new room type IDs
    $roomTypeMap = [
      'ST' => RoomType::where('legacy_code', 'ST')->first()->id,
      'STV' => RoomType::where('legacy_code', 'STV')->first()->id,
      'GR' => RoomType::where('legacy_code', 'GR')->first()->id,
      'FN' => RoomType::where('legacy_code', 'FN')->first()->id,
      'FR' => RoomType::where('legacy_code', 'FR')->first()->id,
      'CY' => RoomType::where('legacy_code', 'CY')->first()->id,
    ];
    
    
    $rooms = [
      // Standard Rooms (ST)
      ['legacy_room_code' => 2, 'number' => '2', 'short_code' => 'Q STD', 'name' => 'Queen Standard', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Queen Standard Room with en-suite bathroom.', 'web_description' => 'Standard - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 2],
      ['legacy_room_code' => 3, 'number' => '3', 'short_code' => 'Q STD', 'name' => 'Queen Standard', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Queen Standard Room with en-suite bathroom.', 'web_description' => 'Standard - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 3],
      ['legacy_room_code' => 4, 'number' => '4', 'short_code' => 'Q STD', 'name' => 'Queen Standard', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Queen Standard Room with en-suite bathroom.', 'web_description' => 'Standard - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 4],
      ['legacy_room_code' => 5, 'number' => '5', 'short_code' => 'Q STD', 'name' => 'Queen Standard', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Queen Standard Room with en-suite bathroom.', 'web_description' => 'Standard - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 5],
      ['legacy_room_code' => 6, 'number' => '6', 'short_code' => 'Q STD', 'name' => 'Queen Standard', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Queen Standard Room with en-suite bathroom.', 'web_description' => 'Standard - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 6],
      ['legacy_room_code' => 7, 'number' => '7', 'short_code' => '2x3/4 STD', 'name' => '2 X 3/4 Standard', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Standard Twin (2 X 3/4) Room with en-suite bathroom.', 'web_description' => 'Standard - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 7],
      ['legacy_room_code' => 8, 'number' => '8', 'short_code' => '2x3/4 STD', 'name' => '2 X 3/4 Standard with Shower', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Standard Twin (2 X 3/4) Room with en-suite bathroom and shower.', 'web_description' => 'Standard - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 8],
      ['legacy_room_code' => 9, 'number' => '9', 'short_code' => '2xSgl STD', 'name' => '2 X Single with Bath', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Standard Twin (2 x Singles) Room with en-suite bathroom.', 'web_description' => 'Standard - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03.jpg', 'display_order' => 9],
      ['legacy_room_code' => 10, 'number' => '10', 'short_code' => '2xSgl STD', 'name' => '2 X Single with Bath', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Standard Twin (2 x Singles) Room with en-suite bathroom.', 'web_description' => 'Standard - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 10],
      ['legacy_room_code' => 11, 'number' => '11', 'short_code' => '2x3/4 STD', 'name' => '2 X 3/4 Standard with Shower', 'room_type_id' => $roomTypeMap['ST'], 'description' => 'Standard Twin (2 x 3/4) Room with en-suite bathroom shower.', 'web_description' => 'Standard - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 11],
      
      // Standard Rooms with TV (STV)
      ['legacy_room_code' => 12, 'number' => '12', 'short_code' => 'Q STD TV', 'name' => 'Queen Standard with TV', 'room_type_id' => $roomTypeMap['STV'], 'description' => 'Standard Queen bed room with en-suite bathroom and TV.', 'web_description' => 'Standard + TV - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 12],
      ['legacy_room_code' => 14, 'number' => '14', 'short_code' => '2xSgl STD TV', 'name' => '2 X Single Standard with TV', 'room_type_id' => $roomTypeMap['STV'], 'description' => 'Standard Twin (2 x Singles) Room with en-suite bathroom and TV.', 'web_description' => 'Standard + TV - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 13],
      ['legacy_room_code' => 16, 'number' => '16', 'short_code' => 'Q STD TV', 'name' => 'Queen Standard with TV', 'room_type_id' => $roomTypeMap['STV'], 'description' => 'Room with en-suite bathroom. Option – Room with TV.', 'web_description' => 'Standard + TV - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_01.jpg', 'display_order' => 14],
      ['legacy_room_code' => 17, 'number' => '17', 'short_code' => '2xSgl STD TV', 'name' => '2 X Single Standard with TV', 'room_type_id' => $roomTypeMap['STV'], 'description' => 'Standard (2 x Single) room with en-suite bathroom and TV.', 'web_description' => 'Standard + TV - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 15],
      ['legacy_room_code' => 18, 'number' => '18', 'short_code' => '2xSgl STD TV', 'name' => '2 X Single Standard with TV', 'room_type_id' => $roomTypeMap['STV'], 'description' => 'Standard twin (2 x Single) room with en-suite bathroom and TV.', 'web_description' => 'Standard + TV - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_standard_room_03_resized.jpg', 'display_order' => 16],
      
      // Garden Suites (GR)
      ['legacy_room_code' => 20, 'number' => '20', 'short_code' => '2x3/4 GDN', 'name' => '2 X 3/4 Garden', 'room_type_id' => $roomTypeMap['GR'], 'description' => 'This is larger than a Standard Room, has a TV and tea-making facilities. The en-suite bathroom has a bath and shower.', 'web_description' => 'Garden Suite - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_garden_room_01.jpg', 'display_order' => 17],
      ['legacy_room_code' => 21, 'number' => '21', 'short_code' => '2x3/4 GDN', 'name' => '2 X 3/4 Garden', 'room_type_id' => $roomTypeMap['GR'], 'description' => 'This is larger than a Standard Room, has a TV and tea-making facilities. The en-suite bathroom has a bath and shower.', 'web_description' => 'Garden Suite - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_garden_room_01.jpg', 'display_order' => 18],
      
      // Fountain Suites (FN)
      ['legacy_room_code' => 15, 'number' => '15', 'short_code' => '2x3/4 FNT', 'name' => '2 X 3/4 Fountain', 'room_type_id' => $roomTypeMap['FN'], 'description' => 'This suite is large, has a TV, fireplace, aircon, lounge area, tea making facilities and a private courtyard. The en-suite bathroom has a bath and shower.', 'web_description' => 'Fountain Suite - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/fountain_suits_1482.jpg', 'display_order' => 19],
      ['legacy_room_code' => 19, 'number' => '19', 'short_code' => '2x3/4 FNT', 'name' => '2 X 3/4 Fountain', 'room_type_id' => $roomTypeMap['FN'], 'description' => 'This suite is large, has a TV, fireplace, aircon, lounge area, tea making facilities and a private courtyard. The en-suite bathroom has a bath and shower.', 'web_description' => 'Fountain Suite - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/fountain_suits_1482.jpg', 'display_order' => 20],
      
      // Forest Suites (FR)
      ['legacy_room_code' => 22, 'number' => '22', 'short_code' => '2x3/4 FST', 'name' => '2 X 3/4 Forest', 'room_type_id' => $roomTypeMap['FR'], 'description' => 'This private, luxurious suite has a lounge with fireplace. The lounge leads out onto a balcony overlooking the forest. Under carpet heating in the bedroom. The air-conditioned suite has a TV with DSTV, tea-making facilities, spa bath and shower.', 'web_description' => 'Forest Suite - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_forest_suite_03.jpg', 'display_order' => 22],
      ['legacy_room_code' => 23, 'number' => '23', 'short_code' => '2x3/4 FST', 'name' => '2 X 3/4 Forest', 'room_type_id' => $roomTypeMap['FR'], 'description' => 'This private, luxurious suite has a lounge with fireplace. The lounge leads out onto a balcony overlooking the forest. Under carpet heating in the bedroom. The air-conditioned suite has a TV with DSTV, tea-making facilities, spa bath and shower.', 'web_description' => 'Forest Suite - Twin', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_forest_suite_03.jpg', 'display_order' => 23],
        
      // Courtyard Suites (CY) - Only active ones
      ['legacy_room_code' => 1, 'number' => '1', 'short_code' => '2XD CY', 'name' => '2 X Double with Courtyard', 'room_type_id' => $roomTypeMap['CY'], 'description' => 'Looks out onto garden courtyard and is near the main facilities. Underfloor heating throughout, fireplace, ceiling fan and tea-making facilities. The separate lounge has a TV with DSTV. Two double beds, bathroom with a bath and shower, and separate toilet.', 'web_description' => 'Courtyard Suite - Double', 'web_image' => 'https://brookdale.co.za/wp-content/uploads/2025/04/BHH_accom_courtyard_suite_01.jpg', 'display_order' => 1],
    ];
    
    foreach ($rooms as $roomData) {
      // create if not exists
      if (Room::where('legacy_room_code', $roomData['legacy_room_code'])->where('property_id', $property->id)->exists()) {
        continue;
      }
      Room::create(array_merge($roomData, [
        'property_id' => $property->id,
        'is_enabled' => true,
        'notes' => 'Imported from legacy system'
      ]));
    }
    
    $this->command->info('Rooms seeded successfully!');
    $this->command->info('Total rooms created: ' . count($rooms) . ' for property ID ' . $property->id);
    
  }
}