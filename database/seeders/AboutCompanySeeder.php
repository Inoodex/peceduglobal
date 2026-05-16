<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\BlockElement;

class AboutCompanySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the About the Company Page
        $page = Page::updateOrCreate(
            ['page_type' => 'about_the_company'],
            [
                'title' => 'About The Company',
                'slug' => 'about-the-company',
                'is_active' => true,
            ]
        );

        // Clear existing blocks
        $page->blocks()->delete();

        // 2. Define Blocks
        
        // Hero Section
        $heroBlock = PageBlock::create([
            'page_id' => $page->id,
            'block_type' => 'hero',
            'section_title' => 'Building Futures, Empowering Dreams',
            'section_description' => 'Pec Edu Global is your trusted partner for international education consultancy since 2010.',
            'sort_order' => 0,
        ]);

        BlockElement::create([
            'page_block_id' => $heroBlock->id,
            'element_title' => 'Start Your Journey',
            'element_body' => 'We help students find their path to top universities across the globe.',
            'image_paths' => ['/storage/defaults/about-hero.jpg'],
            'sort_order' => 0,
        ]);

        // Main Content (Text Left, Image Right)
        $introBlock = PageBlock::create([
            'page_id' => $page->id,
            'block_type' => 'split_content',
            'section_title' => 'Who We Are',
            'section_description' => 'A few words about Pec Edu Global.',
            'sort_order' => 1,
        ]);

        BlockElement::create([
            'page_block_id' => $introBlock->id,
            'element_title' => 'Our Excellence in Education',
            'element_body' => 'Pec Edu Global stands as a beacon of guidance for students aspiring to study abroad. With over a decade of experience, we have successfully assisted thousands of students in securing admissions and visas for top-tier institutions in the UK, USA, Singapore, and beyond. Our personalized approach ensures that every student receives the best possible advice tailored to their academic and career goals.',
            'image_paths' => ['/storage/defaults/about-company.jpg'],
            'sort_order' => 0,
        ]);

        // Mission & Vision (Grid)
        $missionBlock = PageBlock::create([
            'page_id' => $page->id,
            'block_type' => 'grid',
            'section_title' => 'Our Core Values',
            'section_description' => 'Mission and Vision that drive us.',
            'sort_order' => 2,
        ]);

        BlockElement::create([
            'page_block_id' => $missionBlock->id,
            'element_title' => 'Our Mission',
            'element_body' => 'To provide accessible, high-quality consultancy services that bridge the gap between talented students and global education opportunities.',
            'sort_order' => 0,
        ]);

        BlockElement::create([
            'page_block_id' => $missionBlock->id,
            'element_title' => 'Our Vision',
            'element_body' => 'To become the leading education consultancy in Bangladesh, recognized for integrity, student success, and global excellence.',
            'sort_order' => 1,
        ]);

        // Working Process Section (Steps)
        $processBlock = PageBlock::create([
            'page_id' => $page->id,
            'block_type' => 'process',
            'section_title' => 'Our Working Process',
            'section_description' => 'A systematic approach to your global education dreams.',
            'sort_order' => 3,
        ]);

        $steps = [
            ['title' => 'Initial Consultation', 'body' => 'We start with a detailed discussion to understand your goals.'],
            ['title' => 'Course & Country Selection', 'body' => 'Helping you choose the best fit for your career.'],
            ['title' => 'Document Preparation', 'body' => 'Assisting with transcripts, SOPs, and LORs.'],
            ['title' => 'Application Submission', 'body' => 'Ensuring a flawless application to your chosen university.'],
            ['title' => 'Offer Acceptance', 'body' => 'Securing your place at the institution.'],
            ['title' => 'Visa Processing', 'body' => 'Expert guidance for a successful visa interview.'],
            ['title' => 'Pre-departure Briefing', 'body' => 'Getting you ready for your new life abroad.'],
            ['title' => 'Ongoing Support', 'body' => 'We stay with you even after you reach your destination.'],
        ];

        foreach ($steps as $index => $step) {
            BlockElement::create([
                'page_block_id' => $processBlock->id,
                'element_title' => $step['title'],
                'element_body' => $step['body'],
                'sort_order' => $index,
            ]);
        }
    }
}
