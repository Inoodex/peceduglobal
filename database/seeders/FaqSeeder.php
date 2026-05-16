<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\BlockElement;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the FAQ Page
        $page = Page::updateOrCreate(
            ['page_type' => 'faq'],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'is_active' => true,
            ]
        );

        // Clear existing blocks
        $page->blocks()->delete();

        // 2. Define FAQ Block
        $faqBlock = PageBlock::create([
            'page_id' => $page->id,
            'block_type' => 'faq',
            'section_title' => 'General Questions',
            'section_description' => 'Find answers to common queries about studying abroad.',
            'sort_order' => 0,
        ]);

        // 3. Define FAQ Elements (Questions and Answers)
        $faqs = [
            [
                'question' => 'How do I start my application process?',
                'answer' => 'You can start by booking a free consultation with our experts. We will guide you through the initial steps and document requirements.'
            ],
            [
                'question' => 'Which countries do you provide services for?',
                'answer' => 'We primarily provide consultancy for the UK, USA, Canada, Australia, Singapore, and Malaysia.'
            ],
            [
                'question' => 'Is there any fee for the initial consultation?',
                'answer' => 'No, our initial consultation and eligibility assessment are completely free of cost.'
            ],
            [
                'question' => 'Do you help with visa processing?',
                'answer' => 'Yes, we provide complete visa guidance, including document checking and interview preparation.'
            ],
            [
                'question' => 'Can I study abroad with a low CGPA?',
                'answer' => 'Yes, there are many universities that accept students with a moderate CGPA. Our experts can help you find the right fit for your profile.'
            ],
        ];

        foreach ($faqs as $index => $faq) {
            BlockElement::create([
                'page_block_id' => $faqBlock->id,
                'element_title' => $faq['question'],
                'element_body' => $faq['answer'],
                'sort_order' => $index,
            ]);
        }
    }
}
