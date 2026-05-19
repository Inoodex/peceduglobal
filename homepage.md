# 🏠 Next.js Home Page Dynamic Rendering Guide
**Project:** PecEdu Global (Education Consultancy)

এই ডকুমেন্টটি ফ্রন্টএন্ড ডেভেলপারের জন্য তৈরি করা হয়েছে যাতে সে বুঝতে পারে কীভাবে ব্যাকএন্ড API থেকে আসা CMS ডেটা ব্যবহার করে হোম পেজটি ডাইনামিকভাবে রেন্ডার করতে হবে।

---

## 🛠 Core Architecture: Dynamic Component Mapping

হোম পেজটি একটি **Modular Structure** অনুসরণ করে। API থেকে আসা `blocks` অ্যারেটি লুপ করে `block_type` এর উপর ভিত্তি করে নির্দিষ্ট কম্পোনেন্ট রেন্ডার করতে হবে।

### 1. Component Mapping Object
ডেভেলপারকে একটি ম্যাপিং অবজেক্ট তৈরি করতে হবে। উদাহরণস্বরূপ:

```javascript
import HeroSection from '@/components/cms/HeroSection';
import FeatureGrid from '@/components/cms/FeatureGrid';
import FAQSection from '@/components/cms/FAQSection';
import CTABlock from '@/components/cms/CTABlock';
import UniversitySlider from '@/components/cms/UniversitySlider';

const blockComponents = {
  'hero': HeroSection,
  'grid': FeatureGrid,
  'faq': FAQSection,
  'cta': CTABlock,
  'university_slider': UniversitySlider,
  // অন্যান্য টাইপ এখানে যোগ করুন...
};
```

### 2. Dynamic Rendering Logic
পেজ লেভেলে নিচের লজিকটি ব্যবহার করে ব্লকগুলো রেন্ডার করতে হবে:

```jsx
{pageData.blocks.map((block) => {
  const Component = blockComponents[block.block_type];
  return Component ? <Component key={block.id} data={block} /> : null;
})}
```

---

## 🗺 Section-wise Data Mapping (Home Page)

| Block Type | Component | Key Data Fields | UI Implementation |
| :--- | :--- | :--- | :--- |
| **`hero`** | `HeroSection` | `section_title`, `section_description`, `elements` | মেইন ব্যানার, বড় হেডলাইন এবং এলিমেন্ট থেকে বাটন রেন্ডার হবে। |
| **`grid`** | `FeatureGrid` | `section_title`, `settings.subtitle`, `elements` | কার্ড লেআউট। প্রতিটি এলিমেন্টের `element_title` এবং `element_body` কার্ডে বসবে। |
| **`university_slider`** | `UniversitySlider` | `section_title`, `elements` | লোগো স্লাইডার। এলিমেন্টের `image_paths[0]` থেকে লোগো রেন্ডার হবে। |
| **`faq`** | `FAQSection` | `section_title`, `elements` | অ্যাকর্ডিয়ন স্টাইল। `element_title` $\rightarrow$ প্রশ্ন, `element_body` $\rightarrow$ উত্তর। |
| **`cta`** | `CTABlock` | `section_title`, `settings.bg_color`, `elements` | ফুল উইডথ ব্যানার। এলিমেন্ট থেকে বাটন এবং লিঙ্ক রেন্ডার হবে। |

---

## 📦 Data Handling Tips for Developer

### 1. Image Handling (JSON Array)
ব্যাকএন্ড থেকে `image_paths` একটি JSON অ্যারে হিসেবে আসবে। ইমেজ দেখানোর জন্য সবসময় প্রথম ইনডেক্সটি ব্যবহার করুন:
`const imageUrl = block.elements[0].image_paths?.[0] || '/placeholder.jpg';`

### 2. Settings JSON
ব্লকের ডিজাইন কন্ট্রোল করার জন্য `settings` কলামটি ব্যবহার করুন। 
- উদাহরণ: `block.settings.subtitle` $\rightarrow$ সেকশনের উপরের ছোট ক্যাপশন।
- উদাহরণ: `block.settings.bg_color` $\rightarrow$ সেকশনের ব্যাকগ্রাউন্ড কালার।

### 3. Sort Order
ব্লকগুলো রেন্ডার করার আগে অবশ্যই `sort_order` অনুযায়ী সর্ট করে নিতে হবে যাতে অ্যাডমিন প্যানেলের সিকোয়েন্স ঠিক থাকে।

---

## 🚩 Final Checklist for Developer
- [ ] API থেকে `page` ডেটা ফেচ করা হয়েছে।
- [ ] `block_type` অনুযায়ী সঠিক কম্পোনেন্ট ম্যাপ করা হয়েছে।
- [ ] `elements` লুপ করে কার্ড বা লিস্ট তৈরি করা হয়েছে।
- [ ] ইমেজ পাথগুলো সঠিকভাবে রেন্ডার হচ্ছে।
- [ ] রেসপন্সিভ ডিজাইন নিশ্চিত করা হয়েছে।
