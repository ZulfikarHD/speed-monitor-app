---
name: design-system
description: Apply professional, modern, tech-inspired design with full light/dark mode. Use when creating/modifying UI components, layouts, styling, navigation, forms, cards, modals. Auto-applies zinc colors with dark variants, extra dark mode (black base), SVG icons, fake glass effects, smooth transitions, theme-aware effects.
---

# SafeTrack Design System

Professional tech-inspired design with extra dark mode support.

## Core Design Philosophy

**Professional**: Clean lines, consistent spacing, refined typography, proper visual hierarchy

**Modern**: Fake glass effects, gradient overlays, subtle shadows, smooth transitions

**Tech-Inspired**: Grid patterns, monospace fonts for technical elements, cyan/blue color scheme, layered depth

**Theme-Aware**: Full light/dark mode support with deeper, more tech-focused dark mode

## Color Palette (Theme-Aware)

### Dark Mode (Default - Extra Dark & Techy)

#### Backgrounds
- **Base**: `bg-black` or `bg-zinc-950` (pure black or near-black)
- **Primary**: `bg-zinc-900` (very dark surface)
- **Secondary**: `bg-zinc-800` (elevated elements)
- **Tertiary**: `bg-zinc-700` (interactive surfaces)

#### Text
- **Primary**: `text-white` or `text-zinc-50` (brightest)
- **Secondary**: `text-zinc-100` (emphasized)
- **Tertiary**: `text-zinc-200` (regular)
- **Muted**: `text-zinc-400`, `text-zinc-500` (secondary)

#### Accents
- **Primary**: `cyan-400` (active, primary actions)
- **Secondary**: `cyan-500`, `blue-500` (supporting)
- **Bright**: `cyan-300` (highlights)

#### Borders & Effects
- **Borders**: `border-white/5` or `border-zinc-800`
- **Subtle**: `border-white/10` or `border-zinc-700`
- **Glow**: Use cyan/blue with low opacity (5-15%)

#### Grid Patterns (Darker)
```
bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px)]
```
Even subtler grid for darker aesthetic (2% instead of 3%)

### Light Mode

#### Backgrounds
- **Base**: `bg-white` or `bg-zinc-50` (brightest)
- **Primary**: `bg-zinc-100` (main surface)
- **Secondary**: `bg-zinc-200` (elevated)
- **Tertiary**: `bg-zinc-300` (interactive)

#### Text
- **Primary**: `text-zinc-900` (darkest)
- **Secondary**: `text-zinc-800` (emphasized)
- **Tertiary**: `text-zinc-700` (regular)
- **Muted**: `text-zinc-500`, `text-zinc-400` (secondary)

#### Accents
- **Primary**: `cyan-600` (active, primary actions)
- **Secondary**: `cyan-500`, `blue-600` (supporting)
- **Bright**: `cyan-500` (highlights)

#### Borders & Effects
- **Borders**: `border-zinc-200`
- **Subtle**: `border-zinc-300`
- **Shadow**: Use zinc shadows instead of glows

#### Grid Patterns (Light)
```
bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px)]
```
More visible grid for light mode (8% opacity)

### Semantic Colors (Both Modes)
- **Success**: `emerald-500 dark:emerald-400`
- **Warning**: `amber-500 dark:amber-400`
- **Error**: `red-500 dark:red-400`
- **Info**: `cyan-600 dark:cyan-400`

### Usage Pattern
Always use Tailwind's `dark:` variant:
```html
<div class="bg-white dark:bg-black text-zinc-900 dark:text-white">
```

**For complete color specifications with hex values and contrast ratios, see [COLOR_REFERENCE.md](COLOR_REFERENCE.md)**

**For theme implementation details and migration guide, see [THEME_IMPLEMENTATION.md](THEME_IMPLEMENTATION.md)**

## Typography

- **Body**: System font (default)
- **Technical**: `font-mono` for logos/labels
- **Weights**: `font-medium` (500), `font-semibold` (600)
- **Sizes**: `text-sm` (body), `text-base` (emphasis), `text-lg` (headings)
- **Spacing**: `tracking-wide` for technical labels

## Icons

- **SVG only** (NO emojis)
- **2px stroke**, `currentColor`, accepts `size` prop
- Location: `resources/js/components/icons/`

## Visual Effects

### Fake Glass
Lightweight glass-like effect without performance-heavy backdrop-blur.

```html
<!-- Elevated Surface (nav, modals, overlays) -->
<div class="
    bg-white/95 dark:bg-zinc-900/98 
    border border-zinc-200/80 dark:border-white/10 
    shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5
    ring-1 ring-white/20 dark:ring-white/5">
```

**Key Principles:**
- Higher opacity backgrounds (95-98%) for solid appearance
- Double borders: main border + subtle ring for depth
- Soft shadows instead of blur
- Light: zinc shadows, Dark: cyan glows
- NO `backdrop-blur-*` classes

### Tech Grids
```html
<!-- Page (64px) - 8% light, 2% dark -->
<div class="bg-[linear-gradient(...,.08...)...] dark:bg-[linear-gradient(...,.02...)...] bg-[size:64px_64px]">

<!-- Nav (32px) - 5% light, 1% dark -->
<div class="bg-[linear-gradient(...,.05...)...] dark:bg-[linear-gradient(...,.01...)...] bg-[size:32px_32px]">
```

### Gradients
```html
<!-- Backgrounds -->
from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black

<!-- Buttons -->
from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600
```

### Shadows/Glows
```html
<!-- Light: shadows, Dark: glows -->
shadow-lg shadow-zinc-200 dark:shadow-cyan-500/10
```

## Layout Patterns

### Page Layout (Theme-Aware)
```vue
<div class="relative min-h-screen bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black">
    <!-- Tech Grid Background (theme-aware) -->
    <div class="pointer-events-none fixed inset-0 
        bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.08)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.02)_1px,transparent_1px)] 
        bg-[size:64px_64px]">
    </div>
    
    <!-- Radial Gradient Overlay (theme-aware) -->
    <div class="pointer-events-none fixed inset-0 
        bg-[radial-gradient(ellipse_at_top_left,rgba(6,182,212,0.05),transparent_40%)] dark:bg-[radial-gradient(ellipse_at_top_right,rgba(6,182,212,0.08),transparent_50%)]">
    </div>
    
    <!-- Navigation -->
    <TopNav />
    
    <!-- Content -->
    <main class="relative">
        <slot />
    </main>
</div>
```

### Navigation Components
- **Height**: `h-16` (64px) for desktop nav
- **Min Height**: `min-h-[60px]` for mobile bottom nav
- **Backdrop**: Fake glass effect (no blur)
- **Border**: `border-zinc-200/80 dark:border-white/10`
- **Background**: `bg-white/95 dark:bg-zinc-900/98`
- **Ring**: `ring-1 ring-white/20 dark:ring-white/5`
- **Shadow**: `shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5`
- **Grid Overlay**: 32px grid pattern (theme-aware)

### Cards & Containers
```html
<div class="rounded-lg 
    bg-white/95 dark:bg-zinc-800/95 
    border border-zinc-200/80 dark:border-white/10 
    ring-1 ring-white/20 dark:ring-white/5 
    p-6 
    shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5">
```

### Spacing Scale
- **XS**: `gap-1` (4px)
- **SM**: `gap-2` (8px)
- **MD**: `gap-4` (16px)
- **LG**: `gap-6` (24px)
- **XL**: `gap-8` (32px)

## Animations

- **Smooth**: `transition-all duration-200/300`
- **Subtle**: `hover:scale-105`, `hover:-translate-y-1`
- **NO**: Bouncy springs, large rotations, >400ms durations

## Component States

### Active
```html
bg-cyan-100 dark:bg-cyan-500/15 
text-cyan-700 dark:text-cyan-300
shadow-lg shadow-cyan-200 dark:shadow-cyan-500/10
```

### Hover
```html
hover:bg-zinc-100 dark:hover:bg-white/5
hover:text-zinc-900 dark:hover:text-zinc-100
```

### Focus
```html
focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50
focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900
```

## Buttons

### Primary
```html
<button class="px-4 py-2 rounded-lg 
    bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 
    text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 
    hover:shadow-xl active:scale-95">
```

### Secondary
```html
<button class="px-4 py-2 rounded-lg 
    border border-zinc-300 dark:border-white/10 
    bg-white dark:bg-zinc-800/50 
    text-zinc-900 dark:text-zinc-200 
    hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
```

### Ghost
```html
<button class="px-4 py-2 rounded-lg 
    text-zinc-700 dark:text-zinc-300 
    hover:bg-zinc-100 dark:hover:bg-white/5">
```

Touch: 44x44px minimum mobile

## Forms

```html
<input class="w-full px-4 py-2.5 rounded-lg 
    bg-white dark:bg-zinc-800/50 
    border border-zinc-300 dark:border-white/10 
    text-zinc-900 dark:text-zinc-100 
    placeholder-zinc-400 dark:placeholder-zinc-500 
    focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50" />
```

## Theme Implementation

SafeTrack supports full light/dark mode switching with theme persistence.

### Quick Setup
1. Configure Tailwind: `darkMode: 'class'` in `tailwind.config.js`
2. Add `dark:` variants to all color classes
3. Create theme toggle component with `useTheme()` composable
4. Initialize theme on app load

### Theme Toggle Pattern
```vue
<script setup>
import { useTheme } from '@/composables/useTheme';
const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button @click="toggleTheme">
        {{ isDark ? '☀️ Light' : '🌙 Dark' }}
    </button>
</template>
```

**For complete implementation guide, composable code, and migration scripts, see [THEME_IMPLEMENTATION.md](THEME_IMPLEMENTATION.md)**

## Accessibility

### Always Include
- Proper ARIA labels (`aria-label`, `aria-current`, `aria-hidden`)
- Semantic HTML (`<nav>`, `<main>`, `<button>`, etc.)
- Focus states (ring utilities)
- Sufficient color contrast:
  - Light mode: `zinc-900` on `white` (21:1)
  - Dark mode: `white` on `black` (21:1)
- Touch-friendly targets (44x44px minimum)
- Theme toggle accessible via keyboard

## Common Mistakes

### ❌ DON'T
- Emojis as icons (use SVG)
- Playful fonts (use monospace for tech)
- Bouncy animations (use smooth transitions)
- Slate colors (use zinc)
- Forget `dark:` variants
- Light dark mode (use black base)
- `backdrop-blur-*` classes (heavy performance cost)

### ✅ DO
- SVG icons with `currentColor`
- Zinc colors with `dark:` variants
- Extra dark mode (black/zinc-950)
- Smooth 200-300ms transitions
- Fake glass (semi-transparent + borders + shadows)
- Test in both modes

## Quick Reference

```html
<!-- Most used classes -->
bg-white dark:bg-black
bg-zinc-100 dark:bg-zinc-900
text-zinc-900 dark:text-white
border-zinc-200 dark:border-white/5
shadow-lg shadow-zinc-200 dark:shadow-cyan-500/10
hover:bg-zinc-100 dark:hover:bg-white/5
focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50
```

### Component Checklist
- [ ] SVG icons (not emojis)
- [ ] Zinc colors with `dark:` variants
- [ ] Extra dark mode (black base)
- [ ] Smooth transitions (200-300ms)
- [ ] Fake glass effect (no backdrop-blur)
- [ ] Active/hover/focus states
- [ ] ARIA labels
- [ ] 44x44px touch targets mobile
- [ ] Test both light AND dark modes
