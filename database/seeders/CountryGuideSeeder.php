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
            ['name' => 'Singapore', 'iso_code' => 'SG', 'slug' => 'singapore']
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

        // Clear existing blocks to avoid duplicates if re-seeding
        $page->blocks()->delete();

        // 3. Define Blocks and their Elements
        $blocksData = [
            // HERO SECTION
            [
                'block_type' => 'hero',
                'section_title' => 'Study in the Singapore from Bangladesh',
                'section_description' => 'World-class education in the heart of Asia. Secure your future with top-ranked universities and a vibrant multicultural environment.',
                'elements' => [
                    [
                        'element_title' => 'Explore Opportunities',
                        'element_body' => 'Join thousands of Bangladeshi students who chose Singapore for their higher studies.',
                        'image_paths' => ['/storage/defaults/singapore-hero.jpg'],
                        'sort_order' => 0
                    ]
                ]
            ],
            // REASONS TO STUDY
            [
                'block_type' => 'reasons',
                'section_title' => 'Reasons to Study in the Best advice for Singapore',
                'section_description' => 'Why choose Singapore as your next study destination?',
                'elements' => [
                    ['element_title' => 'World-Class Education', 'element_body' => 'Home to some of the world\'s top-ranked universities.', 'sort_order' => 0],
                    ['element_title' => 'Global Hub', 'element_body' => 'A major global hub for business, finance, and innovation.', 'sort_order' => 1],
                    ['element_title' => 'Multicultural Society', 'element_body' => 'A safe, inclusive, and diverse environment for students.', 'sort_order' => 2],
                    ['element_title' => 'Proximity', 'element_body' => 'Easily accessible from Bangladesh with frequent flights.', 'sort_order' => 3],
                ]
            ],
            // SERVICES
            [
                'block_type' => 'services',
                'section_title' => 'Services Designed around You',
                'section_description' => 'We provide end-to-end support for your study abroad journey.',
                'elements' => [
                    ['element_title' => 'Course Counseling', 'element_body' => 'Finding the right course for your career.', 'sort_order' => 0],
                    ['element_title' => 'University Selection', 'element_body' => 'Shortlisting the best institutions based on your profile.', 'sort_order' => 1],
                    ['element_title' => 'Visa Support', 'element_body' => 'Expert guidance on Student Pass application.', 'sort_order' => 2],
                    ['element_title' => 'Scholarship Assistance', 'element_body' => 'Helping you secure financial aid.', 'sort_order' => 3],
                    ['element_title' => 'Accommodation', 'element_body' => 'Finding a safe and comfortable home.', 'sort_order' => 4],
                    ['element_title' => 'Travel Assistance', 'element_body' => 'Booking flights and pre-departure briefing.', 'sort_order' => 5],
                    ['element_title' => 'Part-time Work', 'element_body' => 'Guidance on work regulations for students.', 'sort_order' => 6],
                    ['element_title' => 'Post-study Support', 'element_body' => 'Connecting you with career opportunities.', 'sort_order' => 7],
                ]
            ],
            // PARTNERS
            [
                'block_type' => 'partners',
                'section_title' => 'You\'re chose to partner with Top Best Best Universities',
                'section_description' => 'We represent the most prestigious institutions in Singapore.',
                'elements' => [] // Dynamically filled in frontend from University model
            ],
            // WHY CHOOSE US
            [
                'block_type' => 'why_choose',
                'section_title' => 'Why Choose PEC EDU',
                'section_description' => 'Bangladesh\'s most trusted education consultancy with 15+ years of excellence.',
                'elements' => [
                    [
                        'element_title' => 'Expert Counselors',
                        'element_body' => 'Our team consists of certified experts who understand the Singaporean education system.',
                        'image_paths' => ['/storage/defaults/why-choose-pec.jpg'],
                        'sort_order' => 0
                    ]
                ]
            ],
            // STATISTICS
            [
                'block_type' => 'statistics',
                'section_title' => 'Singapore at a glance',
                'section_description' => 'Key facts and figures about studying in Singapore.',
                'elements' => [
                    ['element_title' => '31%', 'element_body' => 'Success Rate', 'sort_order' => 0],
                    ['element_title' => '24K+', 'element_body' => 'Global Students', 'sort_order' => 1],
                    ['element_title' => '363+', 'element_body' => 'Universities', 'sort_order' => 2],
                    ['element_title' => '35+', 'element_body' => 'Countries Covered', 'sort_order' => 3],
                ]
            ],
            // LIVING COSTS
            [
                'block_type' => 'living_costs',
                'section_title' => 'Living Costs for Bangladeshi Students',
                'section_description' => 'Studying in Singapore requires careful financial planning, as monthly living costs vary by area.',
                'elements' => [
                    ['element_title' => 'Central Area', 'element_body' => 'Accommodation: $800-$1200 | Transport: $100 | Food: $400 | Total: $1300-$1700', 'sort_order' => 0],
                    ['element_title' => 'Jurong East', 'element_body' => 'Accommodation: $600-$900 | Transport: $80 | Food: $350 | Total: $1030-$1330', 'sort_order' => 1],
                    ['element_title' => 'Tampines', 'element_body' => 'Accommodation: $600-$900 | Transport: $80 | Food: $350 | Total: $1030-$1330', 'sort_order' => 2],
                    ['element_title' => 'Woodlands', 'element_body' => 'Accommodation: $550-$850 | Transport: $80 | Food: $350 | Total: $980-$1280', 'sort_order' => 3],
                ]
            ],
            // REQUIREMENTS
            [
                'block_type' => 'requirements',
                'section_title' => 'Requirements for Bangladesh Students to Study abroad',
                'section_description' => 'Basic criteria you need to fulfill to apply for Singapore.',
                'elements' => [
                    ['element_title' => 'Academic GPA', 'element_body' => 'Minimum 3.0 in SSC & HSC.', 'sort_order' => 0],
                    ['element_title' => 'English Proficiency', 'element_body' => 'IELTS 6.0 or equivalent (some private unis allow MOI).', 'sort_order' => 1],
                    ['element_title' => 'Bank Solvency', 'element_body' => 'Proof of funds for tuition and living expenses.', 'sort_order' => 2],
                    ['element_title' => 'Valid Passport', 'element_body' => 'With at least 6 months validity.', 'sort_order' => 3],
                ]
            ]
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
