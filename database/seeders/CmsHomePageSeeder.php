<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\BlockElement;

class CmsHomePageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Home Page
        $homePage = Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home Page',
                'page_type' => 'home',
                'is_active' => true,
            ]
        );

        // --- SECTION 2: About PECEDU Global ---
        $aboutBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'text_content',
            'section_title' => 'About PECEDU Global',
            'section_description' => 'We are a leading education consultancy dedicated to helping students achieve their dreams of studying abroad.',
            'sort_order' => 1,
            'settings' => json_encode(['layout' => 'text-left-image-right']),
        ]);

        BlockElement::create([
            'page_block_id' => $aboutBlock->id,
            'element_type' => 'text',
            'element_title' => 'Your Trusted Partner in Global Education',
            'element_body' => 'PecEdu Global provides comprehensive guidance, from university selection to visa processing, ensuring a seamless transition for students moving to international destinations.',
            'sort_order' => 1,
        ]);

        // --- SECTION 3: Slider Text with Link (Promo/Banner) ---
        $promoBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'cta',
            'section_title' => 'Special Offers & Updates',
            'section_description' => 'Stay updated with the latest scholarship opportunities.',
            'sort_order' => 2,
            'settings' => json_encode(['bg_color' => 'accent']),
        ]);

        BlockElement::create([
            'page_block_id' => $promoBlock->id,
            'element_type' => 'button',
            'element_title' => 'Explore Scholarships 2026',
            'link_url' => '/scholarships',
            'sort_order' => 1, 
        ]);

        // --- SECTION 4: Why Choose Us ---
        $whyChooseBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'grid',
            'section_title' => 'Why Choose PecEdu Global?',
            'section_description' => 'Experience the difference with our expert-led consultancy.',
            'sort_order' => 3,
            'settings' => json_encode(['subtitle' => 'WHY CHOOSE US?']),
        ]);

        $whyChooseElements = [
            ['title' => 'Expert Guidance', 'body' => 'Personalized counseling based on your academic background.', 'type' => 'text'],
            ['title' => 'High Success Rate', 'body' => 'Proven track record of visa approvals for top destinations.', 'type' => 'text'],
            ['title' => 'Global Network', 'body' => 'Strong partnerships with universities in UK, USA, Canada, and Australia.', 'type' => 'text'],
        ];

        foreach ($whyChooseElements as $el) {
            BlockElement::create([
                'page_block_id' => $whyChooseBlock->id,
                'element_type' => $el['type'],
                'element_title' => $el['title'],
                'element_body' => $el['body'],
                'sort_order' => 0,
            ]);
        }

        // --- SECTION 5: We Help Individual Become their Best Version ---
        $bestVersionBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'text_content',
            'section_title' => 'We Help Individuals Become Their Best Version',
            'section_description' => 'Our mission is to empower students to reach their full potential through quality education.',
            'sort_order' => 4,
            'settings' => json_encode(['layout' => 'text-right-image-left']),
        ]);

        BlockElement::create([
            'page_block_id' => $bestVersionBlock->id,
            'element_type' => 'text',
            'element_title' => 'Empowering Your Future',
            'element_body' => 'We believe that education is the most powerful tool to change the world. Our holistic approach ensures students are not just admitted, but prepared for success.',
            'sort_order' => 1,
        ]);

        // --- SECTION 6: Global Clients Around the World ---
        $clientsBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'grid',
            'section_title' => 'Global Clients Around the World',
            'section_description' => 'Thousands of students have trusted us with their future.',
            'sort_order' => 5,
            'settings' => json_encode(['subtitle' => 'OUR REACH']),
        ]);

        $clients = [
            ['title' => 'United Kingdom', 'body' => '500+ Students Placed', 'type' => 'text'],
            ['title' => 'Canada', 'body' => '300+ Students Placed', 'type' => 'text'],
            ['title' => 'USA', 'body' => '200+ Students Placed', 'type' => 'text'],
            ['title' => 'Australia', 'body' => '150+ Students Placed', 'type' => 'text'],
        ];

        foreach ($clients as $cl) {
            BlockElement::create([
                'page_block_id' => $clientsBlock->id,
                'element_type' => $cl['type'],
                'element_title' => $cl['title'],
                'element_body' => $cl['body'],
                'sort_order' => 0,
            ]);
        }

        // --- SECTION 8: Services That We Provide ---
        $servicesBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'grid',
            'section_title' => 'Services That We Provide',
            'section_description' => 'End-to-end support for your international study journey.',
            'sort_order' => 6,
            'settings' => json_encode(['subtitle' => 'OUR SERVICES']),
        ]);

        $services = [
            ['title' => 'University Selection', 'body' => 'Finding the right fit for your academic and career goals.', 'type' => 'text'],
            ['title' => 'Admission Support', 'body' => 'Handling applications, SOPs, and LORs professionally.', 'type' => 'text'],
            ['title' => 'Visa Assistance', 'body' => 'Detailed guidance on visa documentation and interview prep.', 'type' => 'text'],
            ['title' => 'Scholarship Guidance', 'body' => 'Helping you secure the best financial aid available.', 'type' => 'text'],
        ];

        foreach ($services as $sv) {
            BlockElement::create([
                'page_block_id' => $servicesBlock->id,
                'element_type' => $sv['type'],
                'element_title' => $sv['title'],
                'element_body' => $sv['body'],
                'sort_order' => 0,
            ]);
        }

        // --- SECTION 9: Comparison ---
        $compareBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'grid',
            'section_title' => 'Comparison: Why We Are Different',
            'section_description' => 'See how PecEdu Global stands out from other consultancies.',
            'sort_order' => 7,
            'settings' => json_encode(['subtitle' => 'COMPARISON']),
        ]);

        $comparisons = [
            ['title' => 'PecEdu Global', 'body' => 'Personalized 1-on-1 counseling and transparent processing.', 'type' => 'text'],
            ['title' => 'Others', 'body' => 'Generic advice and hidden charges in many cases.', 'type' => 'text'],
        ];

        foreach ($comparisons as $comp) {
            BlockElement::create([
                'page_block_id' => $compareBlock->id,
                'element_type' => $comp['type'],
                'element_title' => $comp['title'],
                'element_body' => $comp['body'],
                'sort_order' => 0,
            ]);
        }

        // --- SECTION 11: Terms and Conditions ---
        $termsBlock = PageBlock::create([
            'page_id' => $homePage->id,
            'block_type' => 'text_content',
            'section_title' => 'Terms and Conditions',
            'section_description' => 'Please read our terms carefully before proceeding with the application.',
            'sort_order' => 8,
            'settings' => json_encode(['layout' => 'full-width']),
        ]);

        BlockElement::create([
            'page_block_id' => $termsBlock->id,
            'element_type' => 'text',
            'element_title' => 'Legal Agreement',
            'element_body' => 'By using our services, you agree to provide authentic documents and follow the guidelines provided by our consultants.',
            'sort_order' => 1,
        ]);
    }
}
