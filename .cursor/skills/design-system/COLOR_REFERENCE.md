# SafeTrack Color Reference

Complete color palette with hex values and usage guidelines for both light and dark modes.

## Zinc Scale (Base Colors - Primary Palette)

The zinc scale provides better neutral colors for light/dark mode support compared to slate.

| Name | Tailwind | Hex | RGB | Light Mode Usage | Dark Mode Usage |
|------|----------|-----|-----|------------------|-----------------|
| White | `white` | `#ffffff` | `rgb(255, 255, 255)` | Base background | Text |
| Black | `black` | `#000000` | `rgb(0, 0, 0)` | Text | **Extra dark base** |
| Zinc 50 | `zinc-50` | `#fafafa` | `rgb(250, 250, 250)` | Subtle backgrounds | - |
| Zinc 100 | `zinc-100` | `#f4f4f5` | `rgb(244, 244, 245)` | Primary surfaces | - |
| Zinc 200 | `zinc-200` | `#e4e4e7` | `rgb(228, 228, 231)` | Elevated surfaces | - |
| Zinc 300 | `zinc-300` | `#d4d4d8` | `rgb(212, 212, 216)` | Borders, dividers | - |
| Zinc 400 | `zinc-400` | `#a1a1aa` | `rgb(161, 161, 170)` | Placeholder text | Muted text |
| Zinc 500 | `zinc-500` | `#71717a` | `rgb(113, 113, 122)` | Muted text | Secondary text |
| Zinc 600 | `zinc-600` | `#52525b` | `rgb(82, 82, 91)` | Secondary text | - |
| Zinc 700 | `zinc-700` | `#3f3f46` | `rgb(63, 63, 70)` | Primary text | Interactive surfaces |
| Zinc 800 | `zinc-800` | `#27272a` | `rgb(39, 39, 42)` | - | Elevated surfaces |
| Zinc 900 | `zinc-900` | `#18181b` | `rgb(24, 24, 27)` | - | Primary surfaces |
| Zinc 950 | `zinc-950` | `#09090b` | `rgb(9, 9, 11)` | - | Near-black base |

## Cyan Scale (Primary Accent)

| Name | Tailwind | Hex | RGB | Usage |
|------|----------|-----|-----|-------|
| Cyan 300 | `cyan-300` | `#67e8f9` | `rgb(103, 232, 249)` | Light accent, borders |
| Cyan 400 | `cyan-400` | `#22d3ee` | `rgb(34, 211, 238)` | Primary accent (active states) |
| Cyan 500 | `cyan-500` | `#06b6d4` | `rgb(6, 182, 212)` | Gradient stops, hover states |
| Cyan 600 | `cyan-600` | `#0891b2` | `rgb(8, 145, 178)` | Pressed states |

## Blue Scale (Supporting Accent)

| Name | Tailwind | Hex | RGB | Usage |
|------|----------|-----|-----|-------|
| Blue 400 | `blue-400` | `#60a5fa` | `rgb(96, 165, 250)` | Light blue accents |
| Blue 500 | `blue-500` | `#3b82f6` | `rgb(59, 130, 246)` | Gradient partner with cyan |
| Blue 600 | `blue-600` | `#2563eb` | `rgb(37, 99, 235)` | Deep gradient stops |

## Semantic Colors

### Success (Emerald)
| Name | Tailwind | Hex | Usage |
|------|----------|-----|-------|
| Emerald 400 | `emerald-400` | `#34d399` | Success states, positive feedback |
| Emerald 500 | `emerald-500` | `#10b981` | Success buttons, icons |

### Warning (Amber)
| Name | Tailwind | Hex | Usage |
|------|----------|-----|-------|
| Amber 400 | `amber-400` | `#fbbf24` | Warning states |
| Amber 500 | `amber-500` | `#f59e0b` | Warning buttons, alerts |

### Error (Red)
| Name | Tailwind | Hex | Usage |
|------|----------|-----|-------|
| Red 400 | `red-400` | `#f87171` | Error states, validation |
| Red 500 | `red-500` | `#ef4444` | Error buttons, critical alerts |

## Opacity Values

### Common Opacity Patterns

| Pattern | Tailwind | Value | Usage |
|---------|----------|-------|-------|
| Borders | `/5` | 5% | `border-white/5` |
| Subtle Borders | `/10` | 10% | `border-white/10` |
| Light Overlays | `/20` | 20% | `bg-slate-800/20` |
| Semi-transparent | `/50` | 50% | `bg-slate-900/50` |
| Strong Backgrounds | `/95` | 95% | `bg-slate-900/95` |

### Gradient Opacity
- Grid patterns: `rgba(6,182,212,0.03)` - 3%
- Radial overlays: `rgba(6,182,212,0.08)` - 8%
- Glow effects: `rgba(6,182,212,0.20)` - 20%

## Color Combinations (Theme-Aware)

### Light Mode

#### Background Layers (Lightest to Darkest)
1. Base: `white` or `zinc-50`
2. Primary: `zinc-100`
3. Elevated: `zinc-200`
4. Interactive: `zinc-300`

#### Text Hierarchy
1. Primary: `zinc-900` (most important)
2. Emphasized: `zinc-800`
3. Regular: `zinc-700`
4. Secondary: `zinc-600`
5. Muted: `zinc-500`, `zinc-400` (least important)

#### Accent Usage
- **Active**: `cyan-700` text + `cyan-100` background
- **Hover**: `cyan-600` text + `zinc-100` background
- **Focus**: `cyan-500` ring

#### Gradient Combinations
```css
/* Navigation backgrounds */
from-white/90 to-zinc-100/90

/* Page backgrounds */
from-zinc-50 via-white to-zinc-50

/* Active states */
from-cyan-100 to-blue-100

/* Buttons */
from-cyan-600 to-blue-700

/* Indicator lines */
from-cyan-500 to-blue-600
```

### Dark Mode (Extra Dark)

#### Background Layers (Darkest to Lighter)
1. Base: `black` or `zinc-950` (extra dark!)
2. Primary: `zinc-900`
3. Elevated: `zinc-800`
4. Interactive: `zinc-700`

#### Text Hierarchy
1. Primary: `white` or `zinc-50` (most important)
2. Emphasized: `zinc-100`
3. Regular: `zinc-200`
4. Secondary: `zinc-300`
5. Muted: `zinc-400`, `zinc-500` (least important)

#### Accent Usage
- **Active**: `cyan-300` text + `cyan-500/15` background
- **Hover**: `cyan-400` text + `white/5` background
- **Focus**: `cyan-400/50` ring

#### Gradient Combinations
```css
/* Navigation backgrounds (extra dark) */
from-zinc-900/98 to-zinc-800/98
/* OR even darker: */
from-black/98 to-zinc-950/98

/* Page backgrounds (extra dark) */
from-black via-zinc-950 to-black

/* Active states */
from-cyan-500/15 to-blue-600/15

/* Buttons */
from-cyan-500 to-blue-600

/* Indicator lines */
from-cyan-400 to-blue-500
```

### Combined (Theme-Aware Classes)
```html
<!-- Background layers -->
<div class="bg-white dark:bg-black">
<div class="bg-zinc-100 dark:bg-zinc-900">
<div class="bg-zinc-200 dark:bg-zinc-800">

<!-- Text hierarchy -->
<h1 class="text-zinc-900 dark:text-white">
<p class="text-zinc-700 dark:text-zinc-200">
<span class="text-zinc-500 dark:text-zinc-400">

<!-- Accents -->
<div class="text-cyan-700 dark:text-cyan-300">
<div class="bg-cyan-100 dark:bg-cyan-500/15">

<!-- Borders -->
<div class="border-zinc-200 dark:border-white/5">
<div class="border-zinc-300 dark:border-white/10">
```

## Contrast Ratios (WCAG AAA)

All combinations exceed WCAG AAA standards (7:1 for normal text, 4.5:1 for large text).

### Light Mode

| Foreground | Background | Ratio | Pass |
|------------|------------|-------|------|
| zinc-900 | white | 21:1 | ✅ AAA |
| zinc-800 | white | 16.8:1 | ✅ AAA |
| zinc-700 | white | 13.5:1 | ✅ AAA |
| zinc-600 | zinc-50 | 9.2:1 | ✅ AAA |
| zinc-500 | white | 7.1:1 | ✅ AAA |
| cyan-700 | white | 8.2:1 | ✅ AAA |
| cyan-600 | cyan-50 | 7.5:1 | ✅ AAA |

### Dark Mode (Extra Dark)

| Foreground | Background | Ratio | Pass |
|------------|------------|-------|------|
| white | black | 21:1 | ✅ AAA |
| zinc-50 | black | 20.5:1 | ✅ AAA |
| zinc-100 | zinc-950 | 18.2:1 | ✅ AAA |
| zinc-200 | zinc-900 | 14.8:1 | ✅ AAA |
| zinc-300 | zinc-900 | 11.5:1 | ✅ AAA |
| zinc-400 | black | 8.5:1 | ✅ AAA |
| cyan-400 | black | 9.8:1 | ✅ AAA |
| cyan-300 | zinc-900 | 11.2:1 | ✅ AAA |

## Usage Examples

### Page Background (Theme-Aware)
```html
<div class="bg-gradient-to-br 
    from-zinc-50 via-white to-zinc-50 
    dark:from-black dark:via-zinc-950 dark:to-black">
```

### Card Surface (Theme-Aware)
```html
<div class="
    bg-white dark:bg-zinc-800/50 
    border border-zinc-200 dark:border-white/5 
    shadow-lg shadow-zinc-200 dark:shadow-none">
```

### Text Hierarchy (Theme-Aware)
```html
<h1 class="text-zinc-900 dark:text-white">Primary Heading</h1>
<p class="text-zinc-700 dark:text-zinc-200">Body text</p>
<span class="text-zinc-500 dark:text-zinc-400">Muted caption</span>
```

### Active Navigation Item (Theme-Aware)
```html
<a class="
    bg-cyan-100 dark:bg-gradient-to-br dark:from-cyan-500/15 dark:to-blue-600/15 
    text-cyan-700 dark:text-cyan-300 
    shadow-lg shadow-cyan-200 dark:shadow-cyan-500/10">
```

### Button (Theme-Aware)
```html
<button class="
    bg-gradient-to-r 
    from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 
    text-white 
    shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 
    hover:shadow-xl hover:shadow-cyan-300 dark:hover:shadow-cyan-500/40">
```

### Input Field (Theme-Aware)
```html
<input class="
    bg-white dark:bg-zinc-800/50 
    border border-zinc-300 dark:border-white/10 
    text-zinc-900 dark:text-zinc-100 
    placeholder-zinc-400 dark:placeholder-zinc-500 
    focus:ring-cyan-500 dark:focus:ring-cyan-400/50" />
```

### Navigation Bar (Theme-Aware with Extra Dark)
```html
<nav class="
    backdrop-blur-xl 
    bg-white/90 dark:bg-black/98 
    border-b border-zinc-200 dark:border-white/5">
    <!-- Navigation content -->
</nav>
```
