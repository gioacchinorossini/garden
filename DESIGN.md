# DESIGN.md — Google Drive UI/UX Design System (Tailwind CSS)

This document serves as the complete UI/UX design specification and Tailwind CSS implementation guide for creating a Google Drive-inspired web application interface.

---

## 1. Design System Overview

The Google Drive aesthetic is based on **Material Design 3 (Material You)**. Key visual principles include:

* **Container-Driven Surface Contrast:** The layout relies on a soft, cool gray-blue background (`bg-[#f0f4f9]`) housing rounded white surface containers (`bg-white rounded-[24px]`).
* **High Corner Radii:** Rounded pill shapes (`rounded-full`) for active navigation items, search bars, and filter chips; large rounded corners (`rounded-2xl` to `rounded-[24px]`) for main containers and cards.
* **Pill Highlights:** Selected and active states utilize full-pill backgrounds (`bg-[#c2e7ff]`) with dark contrast text (`text-[#001d35]`).
* **Subtle Elevation & Outlines:** Minimal reliance on drop shadows. Separation is created using light border lines (`border-[#e1e3e1]`) and subtle background contrasts.

---

## 2. Tailwind CSS Configuration Guide

Extend your `tailwind.config.js` with these tokens to match Google Drive's exact colors, typography, and border radius rules:

```javascript
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        drive: {
          // Canvas & Surfaces
          canvas: "#f0f4f9",
          surface: "#ffffff",
          "surface-hover": "#e9eef6",
          "surface-active": "#c2e7ff",
          "surface-selected": "#e8f0fe",
          
          // Brand & Primary
          primary: "#0b57d0",
          "primary-hover": "#0842a0",
          "primary-text": "#001d35",
          
          // Text Colors
          "text-main": "#1f1f1f",
          "text-sub": "#444746",
          "text-muted": "#747775",
          
          // Borders
          border: "#e1e3e1",
        },
      },
      borderRadius: {
        '3xl': '24px',
        '4xl': '28px',
      },
      fontFamily: {
        sans: ['"Google Sans"', 'Inter', 'Roboto', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
```

---

## 3. Layout Architecture

The overall layout is a 3-column shell with a fixed header, defined with Tailwind classes:

```
+-----------------------------------------------------------------------------------+
| Top Header (`h-16 bg-[#f0f4f9] flex items-center px-4`)                           |
+-------------------+---------------------------------------------------------------+
| Left Sidebar      | Main Surface Area (`flex-1 bg-white rounded-[24px] p-6`)      |
| (`w-64`)          | +-----------------------------------------------------------+ |
|                   | | Action Toolbar (View Toggle, Filters)                     | |
| [+ New] Button    | +-----------------------------------------------------------+ |
| Nav Links         | | Files / Folders (Grid or List Layout)                     | |
| Storage Bar       | +-----------------------------------------------------------+ |
+-------------------+---------------------------------------------------------------+
```

---

## 4. Component Design Specifications & Tailwind Snippets

### 4.1 Outer App Shell
```html
<div class="flex flex-col h-screen w-screen bg-[#f0f4f9] text-[#1f1f1f] overflow-hidden">
  <!-- Top Navigation Bar -->
  <header class="h-16 flex items-center justify-between px-4 z-10">...</header>
  
  <div class="flex flex-1 overflow-hidden pb-4 pr-4">
    <!-- Left Sidebar -->
    <aside class="w-64 flex-shrink-0 px-3 flex flex-col justify-between">...</aside>
    
    <!-- Main Content Container -->
    <main class="flex-1 bg-white rounded-[24px] shadow-sm flex flex-col overflow-hidden">...</main>
  </div>
</div>
```

### 4.2 Top Header & Search Bar
* **Search Bar:** Pill-shaped (`rounded-full`), height `h-12`, max-width `max-w-[720px]`.

```html
<!-- Search Bar Component -->
<div class="flex-1 max-w-[720px] mx-4">
  <div class="relative flex items-center bg-[#e9eef6] focus-within:bg-white focus-within:shadow-md border border-transparent focus-within:border-[#e1e3e1] rounded-full transition-all duration-200">
    <button class="p-3 text-[#444746] hover:bg-black/5 rounded-full ml-1">
      <SearchIcon class="w-5 h-5" />
    </button>
    <input 
      type="text" 
      placeholder="Search in Drive" 
      class="w-full bg-transparent text-[#1f1f1f] placeholder-[#444746] focus:outline-none text-sm py-3"
    />
    <button class="p-3 text-[#444746] hover:bg-black/5 rounded-full mr-1">
      <FilterSlidersIcon class="w-5 h-5" />
    </button>
  </div>
</div>
```

### 4.3 Left Sidebar Navigation & "+ New" Button
* **`+ New` Action Button:** Elevated white/soft-blue rounded box (`rounded-2xl`).
* **Nav Links:** `rounded-full` height `h-10`, active state `#c2e7ff` with dark blue text `#001d35`.

```html
<!-- Floating + New Button -->
<button class="flex items-center gap-3 bg-white hover:bg-[#f8faff] text-[#1f1f1f] font-medium px-5 py-3 rounded-2xl shadow-sm hover:shadow-md transition-all mb-4 border border-[#e1e3e1]/50">
  <PlusIcon class="w-7 h-7 text-[#0b57d0]" />
  <span class="text-sm font-medium">New</span>
</button>

<!-- Navigation Link (Active) -->
<a href="#" class="flex items-center gap-4 px-4 h-10 rounded-full bg-[#c2e7ff] text-[#001d35] font-semibold text-sm">
  <FolderIcon class="w-5 h-5" />
  <span>My Drive</span>
</a>

<!-- Navigation Link (Inactive) -->
<a href="#" class="flex items-center gap-4 px-4 h-10 rounded-full text-[#444746] hover:bg-[#e9eef6] transition-colors text-sm font-medium">
  <SharedIcon class="w-5 h-5" />
  <span>Shared with me</span>
</a>
```

### 4.4 Storage Indicator
```html
<div class="px-4 py-3">
  <div class="flex items-center gap-2 text-xs text-[#444746] mb-2">
    <CloudIcon class="w-4 h-4" />
    <span>Storage</span>
  </div>
  <div class="w-full bg-[#e1e3e1] h-1 rounded-full overflow-hidden mb-1">
    <div class="bg-[#0b57d0] h-full w-[70%]"></div>
  </div>
  <p class="text-xs text-[#444746]">10.5 GB of 15 GB used</p>
  <button class="mt-2 text-xs font-medium text-[#0b57d0] hover:underline">Get more storage</button>
</div>
```

### 4.5 File Grid Card
* **Folder / File Card:** Rounded borders (`rounded-2xl`), subtle hover state.

```html
<div class="group border border-[#e1e3e1] bg-white hover:bg-[#f8faff] rounded-2xl p-4 flex flex-col justify-between transition-all hover:shadow-sm cursor-pointer">
  <div class="flex items-center justify-between mb-3">
    <div class="flex items-center gap-2">
      <FilePdfIcon class="w-6 h-6 text-red-600" />
      <span class="text-sm font-medium text-[#1f1f1f] truncate max-w-[140px]">Project_Spec.pdf</span>
    </div>
    <button class="text-[#444746] opacity-0 group-hover:opacity-100 p-1 hover:bg-black/5 rounded-full transition-opacity">
      <MoreVerticalIcon class="w-4 h-4" />
    </button>
  </div>
  <!-- Thumbnail Preview Container -->
  <div class="h-32 bg-[#f8fafd] rounded-xl flex items-center justify-center border border-[#e1e3e1]/40 overflow-hidden">
    <img src="/preview-thumbnail.png" alt="Preview" class="object-cover h-full w-full" />
  </div>
</div>
```

### 4.6 List View Row
```html
<div class="flex items-center justify-between h-12 px-4 border-b border-[#f0f4f9] hover:bg-[#f8faff] text-sm text-[#1f1f1f] group cursor-pointer transition-colors">
  <div class="flex items-center gap-3 flex-1">
    <input type="checkbox" class="rounded border-[#747775] text-[#0b57d0] focus:ring-0" />
    <FileDocIcon class="w-5 h-5 text-blue-600" />
    <span class="font-medium truncate">Quarterly Report.docx</span>
  </div>
  <div class="w-36 text-[#444746] text-xs">Me</div>
  <div class="w-36 text-[#444746] text-xs">Yesterday</div>
  <div class="w-24 text-[#444746] text-xs">2.4 MB</div>
  <div class="w-10 flex justify-end">
    <button class="text-[#444746] opacity-0 group-hover:opacity-100 p-1 hover:bg-black/5 rounded-full transition-opacity">
      <MoreVerticalIcon class="w-4 h-4" />
    </button>
  </div>
</div>
```

---

## 5. Design Token Reference Table

| Role | Utility / Value | Purpose |
| :--- | :--- | :--- |
| Outer Canvas | `bg-[#f0f4f9]` | Deep neutral gray-blue outer application shell |
| Main Workspace Surface | `bg-white rounded-[24px]` | Main rounded panel hosting files/folders |
| Primary Action Button | `bg-white rounded-2xl border-[#e1e3e1]` | Signature `+ New` button styling |
| Active Nav State | `bg-[#c2e7ff] text-[#001d35] rounded-full` | Selected menu item pill highlight |
| Hover Feedback State | `hover:bg-[#e9eef6]` or `hover:bg-black/5` | Soft hover fill for items and icon buttons |
| Primary Accent Text | `text-[#0b57d0]` | Drive primary blue links and active indicators |
| Card Container | `border border-[#e1e3e1] rounded-2xl` | Standard file/folder container border |
