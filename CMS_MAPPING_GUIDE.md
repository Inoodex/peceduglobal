# 📄 CMS to Frontend Data Mapping Guide
**Project:** PecEdu Global (Education Consultancy)

এই ডকুমেন্টটি ফ্রন্টএন্ড ডেভেলপারকে সাহায্য করবে যাতে সে বুঝতে পারে ব্যাকএন্ড API থেকে আসা ডেটা কীভাবে UI-তে রেন্ডার করতে হবে।

## 🛠 Core Logic (কিভাবে কাজ করবে)
API থেকে যখন একটি `Page` এর ডেটা আসবে, তখন সেখানে `blocks` এর একটি অ্যারে থাকবে। প্রতিটি ব্লকের `block_type` দেখে আপনাকে নির্দিষ্ট কম্পোনেন্ট কল করতে হবে।

**Logic Flow:**
`Page` $\rightarrow$ `Loop through blocks` $\rightarrow$ `Check block_type` $\rightarrow$ `Render Component` $\rightarrow$ `Loop through elements`

---

## 🗺 Block Mapping Table

| Block Type (`block_type`) | Frontend Component | Data Usage (কিভাবে দেখাবে) |
| :--- | :--- | :--- |
| **`hero`** | `HeroSection.jsx` | `section_title` $\rightarrow$ Main Heading <br> `section_description` $\rightarrow$ Sub-heading <br> `elements` $\rightarrow$ Hero Images/Buttons |
| **`grid`** | `FeatureGrid.jsx` | `section_title` $\rightarrow$ Section Title <br> `elements` $\rightarrow$ Each element is a Grid Card (Title, Body, Image) |
| **`university_list`** | `UniversitySlider.jsx` | `section_title` $\rightarrow$ Title <br> `elements` $\rightarrow$ University Cards (Logo, Name, Link) |
| **`scholarship_list`** | `ScholarshipGrid.jsx` | `section_title` $\rightarrow$ Title <br> `elements` $\rightarrow$ Scholarship Details (Amount, Eligibility, Link) |
| **`faq`** | `FAQAccordion.jsx` | `section_title` $\rightarrow$ Title <br> `elements` $\rightarrow$ Question (`element_title`) & Answer (`element_body`) |
| **`cta`** | `CallToAction.jsx` | `section_title` $\rightarrow$ Heading <br> `elements` $\rightarrow$ Primary/Secondary Buttons |
| **`team`** | `TeamSection.jsx` | `section_title` $\rightarrow$ Title <br> `elements` $\rightarrow$ Member Photo, Name, Designation |

---

## 📦 Data Structure Example (JSON)

ডেভেলপারকে বলুন এই স্ট্রাকচারটি ফলো করতে:

```json
{
  "title": "Study in UK",
  "blocks": [
    {
      "id": 1,
      "block_type": "hero",
      "section_title": "Your Future Starts Here",
      "section_description": "Get expert guidance for UK Universities",
      "settings": { "bg_color": "blue", "alignment": "center" },
      "elements": [
        {
          "element_title": "Apply Now",
          "link_url": "/apply",
          "image_paths": ["hero-bg.jpg"]
        }
      ]
    },
    {
      "id": 2,
      "block_type": "grid",
      "section_title": "Our Services",
      "elements": [
        {
          "element_title": "Visa Assistance",
          "element_body": "We help you with all visa paperwork.",
          "image_paths": ["visa-icon.png"]
        },
        {
          "element_title": "University Selection",
          "element_body": "Find the best course for your career.",
          "image_paths": ["uni-icon.png"]
        }
      ]
    }
  ]
}
```

---

## 💡 Developer Instructions (ডেভেলপারের জন্য নোট)

1. **Dynamic Rendering:** `block_type` এর উপর ভিত্তি করে একটি `switch` কেস বা `mapping object` তৈরি করো।
2. **Image Handling:** `image_paths` এখন একটি অ্যারে (JSON), তাই প্রথম ইমেজটি (`image_paths[0]`) ডিফল্ট হিসেবে ব্যবহার করো।
3. **Sorting:** ব্যাকএন্ড থেকে আসা `sort_order` অনুযায়ী সেকশনগুলো রেন্ডার করো।
4. **Fallback:** যদি কোনো `block_type` অজানা হয়, তবে সেটি রেন্ডার না করে স্কিপ করো।
