<template>
  <div v-if="editor" class="editor-container border border-gray-200 dark:border-gray-700/50 rounded-2xl overflow-hidden bg-white dark:bg-[#1C252E] shadow-sm">
    <!-- Toolbar -->
    <div class="toolbar flex flex-wrap items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700/50 bg-gray-50 dark:bg-[#141A21]">
      <!-- Formatting -->
      <div class="flex items-center gap-1 pr-2 mr-2 border-r border-gray-200 dark:border-gray-700/50">
        <button type="button" @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }" class="toolbar-btn" title="Bold"><Bold class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }" class="toolbar-btn" title="Italic"><Italic class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'is-active': editor.isActive('underline') }" class="toolbar-btn" title="Underline"><UnderlineIcon class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'is-active': editor.isActive('strike') }" class="toolbar-btn" title="Strike"><Strikethrough class="w-4 h-4" /></button>
      </div>

      <!-- Headings -->
      <div class="flex items-center gap-1 pr-2 mr-2 border-r border-gray-200 dark:border-gray-700/50">
        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }" class="toolbar-btn" title="H1"><Heading1 class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }" class="toolbar-btn" title="H2"><Heading2 class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }" class="toolbar-btn" title="H3"><Heading3 class="w-4 h-4" /></button>
      </div>

      <!-- Alignment & Floating -->
      <div class="flex items-center gap-1 pr-2 mr-2 border-r border-gray-200 dark:border-gray-700/50">
        <button type="button" @click="editor.chain().focus().setTextAlign('left').run()" :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }" class="toolbar-btn" title="Align Left"><AlignLeft class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().setTextAlign('center').run()" :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }" class="toolbar-btn" title="Align Center"><AlignCenter class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().setTextAlign('right').run()" :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }" class="toolbar-btn" title="Align Right"><AlignRight class="w-4 h-4" /></button>
        <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></div>
        <button type="button" @click="toggleFloat('left')" :class="{ 'is-active': isFloatActive('left') }" class="toolbar-btn" title="Wrap Left"><ArrowLeftToLine class="w-4 h-4" /></button>
        <button type="button" @click="toggleFloat('right')" :class="{ 'is-active': isFloatActive('right') }" class="toolbar-btn" title="Wrap Right"><ArrowRightToLine class="w-4 h-4" /></button>
      </div>

      <!-- Lists & Quote -->
      <div class="flex items-center gap-1 pr-2 mr-2 border-r border-gray-200 dark:border-gray-700/50">
        <button type="button" @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor.isActive('bulletList') }" class="toolbar-btn" title="Bullet List"><List class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': editor.isActive('orderedList') }" class="toolbar-btn" title="Ordered List"><ListOrdered class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': editor.isActive('blockquote') }" class="toolbar-btn" title="Quote"><Quote class="w-4 h-4" /></button>
      </div>

      <!-- Table -->
      <div class="flex items-center gap-1 pr-2 mr-2 border-r border-gray-200 dark:border-gray-700/50">
        <button type="button" @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()" class="toolbar-btn" title="Insert Table"><TableIcon class="w-4 h-4" /></button>
        <div v-if="editor.isActive('table')" class="flex items-center gap-1 ml-1 bg-primary/10 rounded-lg px-1">
          <button type="button" @click="editor.chain().focus().addColumnBefore().run()" class="toolbar-btn-sm" title="Add Col Before"><ChevronLeft class="w-3 h-3" /></button>
          <button type="button" @click="editor.chain().focus().addColumnAfter().run()" class="toolbar-btn-sm" title="Add Col After"><ChevronRight class="w-3 h-3" /></button>
          <button type="button" @click="editor.chain().focus().addRowBefore().run()" class="toolbar-btn-sm" title="Add Row Before"><ChevronUp class="w-3 h-3" /></button>
          <button type="button" @click="editor.chain().focus().addRowAfter().run()" class="toolbar-btn-sm" title="Add Row After"><ChevronDown class="w-3 h-3" /></button>
          <button type="button" @click="editor.chain().focus().deleteTable().run()" class="toolbar-btn-sm text-red-500 hover:bg-red-50" title="Delete Table"><Trash2 class="w-3 h-3" /></button>
        </div>
      </div>

      <!-- Misc -->
      <div class="flex items-center gap-1">
        <button type="button" @click="setLink" :class="{ 'is-active': editor.isActive('link') }" class="toolbar-btn" title="Link"><LinkIcon class="w-4 h-4" /></button>
        <button type="button" @click="triggerImageUpload" :disabled="uploading" class="toolbar-btn" title="Upload Image">
          <Loader2 v-if="uploading" class="w-4 h-4 animate-spin" />
          <ImageIcon v-else class="w-4 h-4" />
        </button>
        <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload" />
        <button type="button" @click="editor.chain().focus().undo().run()" class="toolbar-btn" title="Undo"><Undo class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().redo().run()" class="toolbar-btn" title="Redo"><Redo class="w-4 h-4" /></button>
        <button type="button" @click="editor.chain().focus().unsetAllMarks().run()" class="toolbar-btn text-red-500" title="Clear Formatting"><Eraser class="w-4 h-4" /></button>
      </div>
    </div>

    <!-- Editor Area -->
    <EditorContent :editor="editor" class="prose prose-sm dark:prose-invert max-w-none p-4 min-h-[350px] outline-none" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { Underline } from '@tiptap/extension-underline';
import { Link } from '@tiptap/extension-link';
import ImageResize from 'tiptap-extension-resize-image';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableCell } from '@tiptap/extension-table-cell';
import { TextAlign } from '@tiptap/extension-text-align';
import { Highlight } from '@tiptap/extension-highlight';
import { Color } from '@tiptap/extension-color';
import { TextStyle } from '@tiptap/extension-text-style';
import axios from '@/plugins/axios';

import {
  Bold, Italic, Underline as UnderlineIcon, Strikethrough,
  Heading1, Heading2, Heading3,
  AlignLeft, AlignCenter, AlignRight,
  List, ListOrdered, Quote,
  Table as TableIcon, ChevronLeft, ChevronRight, ChevronUp, ChevronDown, Trash2,
  Link as LinkIcon, Image as ImageIcon, Undo, Redo, Eraser, Loader2,
  ArrowLeftToLine, ArrowRightToLine
} from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const uploading = ref(false);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
      link: false,
      underline: false,
    }),
    Underline,
    Link.configure({
      openOnClick: false,
    }),
    ImageResize.configure({
      inline: true,
      HTMLAttributes: {
        class: 'resizable-image',
      },
    }),
    Table.configure({
      resizable: true,
    }),
    TableRow,
    TableHeader,
    TableCell,
    TextAlign.configure({
      types: ['heading', 'paragraph', 'image'],
    }),
    Highlight,
    TextStyle,
    Color,
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
});

const toggleFloat = (direction) => {
  if (!editor.value.isActive('image')) return;
  
  const currentAttributes = editor.value.getAttributes('image');
  const currentFloat = currentAttributes.style?.includes(`float: ${direction}`) ? null : direction;
  
  if (!currentFloat) {
    editor.value.chain().focus().updateAttributes('image', { style: null }).run();
  } else {
    editor.value.chain().focus().updateAttributes('image', { 
      style: `float: ${direction}; margin: ${direction === 'left' ? '0 20px 20px 0' : '0 0 20px 20px'}; display: inline-block;` 
    }).run();
  }
};

const isFloatActive = (direction) => {
  if (!editor.value) return false;
  const attr = editor.value.getAttributes('image');
  return attr.style?.includes(`float: ${direction}`);
};

const setLink = () => {
  const previousUrl = editor.value.getAttributes('link').href;
  const url = window.prompt('URL', previousUrl);

  if (url === null) return;
  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }

  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const triggerImageUpload = () => {
  fileInput.value.click();
};

const handleImageUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('image', file);

  uploading.value = true;
  try {
    const response = await axios.post('/auth/admin/editor/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    const url = response.data.url;
    editor.value.chain().focus().setImage({ src: url }).run();
  } catch (error) {
    console.error('Image upload failed', error);
    alert('Failed to upload image. Please try again.');
  } finally {
    uploading.value = false;
    event.target.value = ''; // Reset input
  }
};

// Sync with prop
watch(() => props.modelValue, (value) => {
  if (!editor.value) return;
  const isSame = editor.value.getHTML() === value;
  if (!isSame) {
    editor.value.commands.setContent(value, false);
  }
});
</script>

<style>
@reference "../../css/app.css";

/* Tiptap specific styles */
.ProseMirror {
  outline: none !important;
}

.toolbar-btn {
  @apply p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors;
}

.toolbar-btn.is-active {
  @apply bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light;
}

.toolbar-btn-sm {
  @apply p-1 rounded-md text-gray-600 dark:text-gray-400 hover:bg-white/50 dark:hover:bg-gray-800 transition-colors;
}

/* Image Resize Styles */
.ProseMirror img {
  @apply cursor-pointer transition-all;
  display: inline-block !important;
  vertical-align: middle !important;
  max-width: 100%;
}

.ProseMirror img.ProseMirror-selectednode {
  @apply ring-4 ring-primary/30 outline-none rounded-lg;
}

/* Ensure wrappers don't break the inline flow */
.ProseMirror *:has(> img.resizable-image) {
  display: inline-block !important;
  vertical-align: middle !important;
}

/* Ensure text can wrap around floated images properly */
.ProseMirror p {
  clear: none;
}
.ProseMirror::after {
  content: "";
  display: table;
  clear: both;
}

/* Table styles inside editor */
.ProseMirror table {
  border-collapse: collapse;
  table-layout: fixed;
  width: 100%;
  margin: 1.5rem 0;
  overflow: hidden;
}

.ProseMirror td,
.ProseMirror th {
  min-width: 1em;
  border: 2px solid #ced4da;
  padding: 10px 12px;
  vertical-align: top;
  box-sizing: border-box;
  position: relative;
}

.dark .ProseMirror td,
.dark .ProseMirror th {
  border-color: #374151;
}

.ProseMirror th {
  font-weight: bold;
  text-align: left;
  background-color: #f1f3f5;
}

.dark .ProseMirror th {
  background-color: #141A21;
}

.ProseMirror .selectedCell:after {
  z-index: 2;
  position: absolute;
  content: "";
  left: 0; right: 0; top: 0; bottom: 0;
  background: rgba(200, 200, 255, 0.4);
  pointer-events: none;
}
</style>
