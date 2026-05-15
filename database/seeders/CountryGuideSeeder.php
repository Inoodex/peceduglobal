<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\BlockElement;
use App\Models\Country;

class CountryGuideSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Singapore Country exists
        $country = Country::firstOrCreate(
            ['name' => 'Singapore'],
            ['name' => 'Singapore', 'iso_code' => 'SG']
        );

        // 2. Create the Singapore Guide Page
        $page = Page::updateOrCreate(
            ['page_type' => 'country_guide', 'country_id' => $country->id],
            [
                'title' => 'Study in Singapore from Bangladesh',
                'slug' => 'singapore-from-bangladesh',
                'is_active' => true,
            ]
        );

        // 3. Define Blocks and their Elements
        $blocksData = [
            [
                'block_type' => 'country_hero',
                'section_title' => 'Study in Singapore from Bangladesh',
                'section_description' => 'Your gateway to world-class education and a thriving career in the heart of Asia.',
                'elements' => [
                    [
                        'element_title' => '',
                        'element_body' => '',
                        'image_paths' => [], // Admin will upload actual image
                        'sort_order' => 0
                    ]
                ]
            ],
            [
                'block_type' => 'country_intro',
                'section_title' => 'About Singapore',
                'section_description' => '',
                'elements' => [
                    [
                        'element_title' => '',
                        'element_body' => 'Singapore is a global hub for education and innovation. Known for its world-class universities and high standard of living, it is a top destination for Bangladeshi students seeking quality education and career growth.',
                        'sort_order' => 0
                    ]
                ]
            ],
            [
                'block_type' => 'living_costs',
                'section_title' => 'Living Costs in Singapore',
                'section_description' => 'Understanding the cost of living is crucial for planning your studies.',
                'elements' => [
                    [
                        'element_title' => 'Accommodation',
                        'element_body' => '$500 - $1,200 per month',
                        'sort_order' => 0
                    ],
                    [
                        'element_title' => 'Food & Groceries',
                        'element_body' => '$300 - $600 per month',
                        'sort_order' => 1
                    ],
                    [
                        'element_title' => 'Transport',
                        'element_body' => '$50 - $150 per month',
                        'sort_order' => 2
                    ],
                    [
                        'element_title' => 'Miscellaneous',
                        'element_body' => '$100 - $300 per month',
                        'sort_order' => 3
                    ]
                ]
            ],
            [
                'block_type' => 'requirements',
                'section_title' => 'Required Documents',
                'section_description' => 'Ensure you have all the necessary documents for a smooth application process.',
                'elements' => [
                    [
                        'element_title' => 'Academic Transcripts',
                        'element_body' => 'SSC and HSC certificates with minimum required GPA.',
                        'sort_order' => 0
                    ],
                    [
                        'element_title' => 'English Proficiency',
                        'element_body' => 'IELTS (usually 6.0 - 6.5) or PTE/TOEFL equivalent.',
                        'sort_order' => 1
                    ],
                    [
                        'element_title' => 'Passport',
                        'element_body' => 'Valid passport with at least 6 months validity.',
                        'sort_order' => 2
                    ],
                    [
                        'element_title' => 'SOP & LOR',
                        'element_body' => 'A strong Statement of Purpose and Letters of Recommendation.',
                        'sort_order' => 3
                    ]
                ]
            ],
            [
                'block_type' => 'country_facts',
                'section_title' => 'Facts at a Glance',
                'section_description' => '',
                'elements' => [
                    [
                        'element_title' => 'Currency',
                        'element_body' => 'Singapore Dollar (SGD)',
                        'sort_order' => 0
                    ],
                    [
                        'element_title' => 'Official Language',
                        'element_body' => 'English, Mandarin, Malay, Tamil',
                        'sort_order' => 1
                    ],
                    [
                        'element_title' => 'Climate',
                        'element_body' => 'Tropical (Hot and Humid)',
                        'sort_order' => 2
                    ],
                    [
                        'element_title' => 'Time Zone',
                        'element_body' => 'GMT+8',
                        'sort_order' => 3
                    ]
                ]
            ],
            [
                'block_type' => 'university_list',
                'section_title' => 'Top Universities in Singapore',
                'section_description' => 'Explore some of the most prestigious institutions in the country.',
                'elements' => [] // Data comes from University model
            ],
            [
                'block_type' => 'visa_process',
                'section_title' => 'Visa Application Process',
                'section_description' => 'A step-by-step guide to securing your student visa for Singapore.',
                'elements' => [
                    [
                        'element_title' => 'Step 1: University Admission',
                        'element_body' => 'Apply to your chosen university and receive an unconditional offer letter.',
                        'sort_order' => 0
                    ],
                    [
                        'element_title' => 'Step 2: Student Pass (STP) Application',
                        'element_body' => 'Apply for the Student\'s Pass via the SOLAR system.',
                        'sort_order' => 1
                    ],
                    [
                        'element_title' => 'Step 3: Visa Approval',
                        'element_body' => 'Wait for the In-Principle Approval (IPA) letter from ICA.',
                        'sort_order' => 2
                    ],
                    [
                        'element_title' => 'Step 4: Travel to Singapore',
                        'element_body' => 'Fly to Singapore and complete the formalities for your STP card.',
                        'sort_order' => 3
                    ]
                ]
            ],
        ];

        foreach ($blocksData as $index => $blockData) {
            $block = PageBlock::create([
                'page_id' => $page->id,
                'block_type' => $blockData['block_type'],
                'section_title' => $blockData['section_title'],
                'section_description' => $blockData['section_description'],
                'sort_order' => $index,
            ]);

            foreach ($blockData['elements'] as $elementData) {
                BlockElement::create([
                    'page_block_id' => $block->id,
                    'element_title' => $elementData['element_title'],
                    'element_body' => $elementData['element_body'],
                    'image_paths' => $elementData['image_paths'] ?? [],
                    'sort_order' => $elementData['sort_order'],
                ]);
            }
        }
    }
}
