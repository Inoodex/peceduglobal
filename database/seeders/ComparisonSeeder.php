<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\BlockElement;

class ComparisonSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Comparison Page
        $page = Page::updateOrCreate(
            ['page_type' => 'comparison'],
            [
                'title' => 'Country Comparison Guide',
                'slug' => 'comparison',
                'is_active' => true,
            ]
        );

        // Clear existing blocks
        $page->blocks()->delete();

        // 2. Define Comparison Criteria (Blocks) and Consultancy Data (Elements)
        $comparisonData = [
            ['criteria' => 'Authorised by International Universities', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Global SIM Card on Arrival', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Part-time Job Assistance', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Airport Pickup', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Health Insurance Guidance', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Internship Placement Support', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'International Payment Services', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'UK Bank Account Setup', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Cheap Air Ticket Assistance', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'IELTS Registration Cashback', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Welcome Gift Hamper', 'other' => 'No', 'pec' => 'Yes'],
            ['criteria' => 'Free Eligibility Assessment', 'other' => 'Yes', 'pec' => 'Yes'],
        ];

        foreach ($comparisonData as $index => $row) {
            $block = PageBlock::create([
                'page_id' => $page->id,
                'block_type' => 'comparison_row',
                'section_title' => $row['criteria'],
                'section_description' => '',
                'sort_order' => $index,
            ]);

            // Other Consultancy Element
            BlockElement::create([
                'page_block_id' => $block->id,
                'element_title' => 'Other Consultancy',
                'element_body' => $row['other'],
                'sort_order' => 0,
            ]);

            // Pec Edu Element
            BlockElement::create([
                'page_block_id' => $block->id,
                'element_title' => 'Pec Edu',
                'element_body' => $row['pec'],
                'sort_order' => 1,
            ]);
        }
    }
}
