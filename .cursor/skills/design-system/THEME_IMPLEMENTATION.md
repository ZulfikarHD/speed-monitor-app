# Theme Implementation Guide

Complete guide for implementing light/dark mode in SafeTrack with extra dark mode.

## Setup

### 1. Configure Tailwind

**tailwind.config.js**:
```js
/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Enable class-based dark mode
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            // Add custom dark mode colors if needed
        },
    },
}
```

### 2. Create Theme Composable

**resources/js/composables/useTheme.ts**:
```typescript
import { ref, onMounted, watch } from 'vue';

type Theme = 'light' | 'dark';

const isDark = ref<boolean>(false);
const theme = ref<Theme>('light');

export function useTheme() {
    /**
     * Initialize theme from localStorage or system preference
     */
    function initTheme(): void {
        const savedTheme = localStorage.getItem('theme') as Theme | null;
        
        if (savedTheme) {
            // Use saved preference
            setTheme(savedTheme);
        } else {
            // Use system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            setTheme(prefersDark ? 'dark' : 'light');
        }
    }

    /**
     * Set theme and update DOM
     */
    function setTheme(newTheme: Theme): void {
        theme.value = newTheme;
        isDark.value = newTheme === 'dark';
        
        // Update DOM
        if (newTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Persist preference
        localStorage.setItem('theme', newTheme);
    }

    /**
     * Toggle between light and dark mode
     */
    function toggleTheme(): void {
        setTheme(isDark.value ? 'light' : 'dark');
    }

    /**
     * Listen for system preference changes
     */
    function watchSystemPreference(): void {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', (e) => {
            // Only auto-switch if user hasn't set a preference
            if (!localStorage.getItem('theme')) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    // Initialize on mount
    onMounted(() => {
        initTheme();
        watchSystemPreference();
    });

    return {
        isDark,
        theme,
        toggleTheme,
        setTheme,
    };
}
```

### 3. Create Theme Toggle Component

**resources/js/components/common/ThemeToggle.vue**:
```vue
<script setup lang="ts">
import { useTheme } from '@/composables/useTheme';
import { IconSun, IconMoon } from '@/components/icons';

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button
        @click="toggleTheme"
        class="group relative flex h-10 w-10 items-center justify-center rounded-lg 
            border border-zinc-200 dark:border-white/10 
            bg-white dark:bg-zinc-800/50 
            text-zinc-700 dark:text-zinc-300 
            transition-all duration-200 
            hover:bg-zinc-50 dark:hover:bg-zinc-700/50 
            hover:border-zinc-300 dark:hover:border-white/20 
            focus:outline-none 
            focus:ring-2 
            focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <!-- Sun icon (light mode) -->
        <IconSun
            v-if="!isDark"
            :size="20"
            class="transition-transform duration-200 group-hover:rotate-45"
        />
        
        <!-- Moon icon (dark mode) -->
        <IconMoon
            v-else
            :size="20"
            class="transition-transform duration-200 group-hover:-rotate-12"
        />
    </button>
</template>
```

### 4. Create Sun/Moon Icons

**resources/js/components/icons/IconSun.vue**:
```vue
<script setup lang="ts">
interface Props {
    size?: number;
}
withDefaults(defineProps<Props>(), { size: 24 });
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
    </svg>
</template>
```

**resources/js/components/icons/IconMoon.vue**:
```vue
<script setup lang="ts">
interface Props {
    size?: number;
}
withDefaults(defineProps<Props>(), { size: 24 });
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
</template>
```

### 5. Add Theme Toggle to Navigation

Update `TopNav.vue`:
```vue
<script setup>
import ThemeToggle from '@/components/common/ThemeToggle.vue';
// ... other imports
</script>

<template>
    <nav>
        <!-- ... navigation items ... -->
        
        <!-- Right: Theme Toggle + User Profile -->
        <div class="flex items-center gap-3">
            <ThemeToggle />
            <UserProfileDropdown />
        </div>
    </nav>
</template>
```

## Migration from Slate to Zinc

Since we're now using zinc instead of slate for better light/dark support:

### Find & Replace Guide

| Old (Slate) | New (Zinc + Dark Mode) |
|-------------|------------------------|
| `bg-slate-950` | `bg-black dark:bg-black` or `bg-zinc-50 dark:bg-black` |
| `bg-slate-900` | `bg-white dark:bg-zinc-900` |
| `bg-slate-800` | `bg-zinc-100 dark:bg-zinc-800` |
| `text-slate-100` | `text-zinc-900 dark:text-white` |
| `text-slate-400` | `text-zinc-600 dark:text-zinc-400` |
| `border-[#3E3E3A]` | `border-zinc-200 dark:border-white/5` |

### Automated Migration Script

```bash
#!/bin/bash
# migrate-to-theme.sh

# Find all Vue files and replace slate with zinc + dark mode
find resources/js -name "*.vue" -type f -exec sed -i \
    -e 's/bg-slate-950/bg-white dark:bg-black/g' \
    -e 's/bg-slate-900/bg-zinc-100 dark:bg-zinc-900/g' \
    -e 's/bg-slate-800/bg-zinc-200 dark:bg-zinc-800/g' \
    -e 's/text-slate-100/text-zinc-900 dark:text-white/g' \
    -e 's/text-slate-400/text-zinc-600 dark:text-zinc-400/g' \
    {} \;

echo "Migration complete! Review changes with 'git diff'"
```

## Dark Mode Best Practices

### 1. Extra Dark Base
Always use pure black or zinc-950 for dark mode base:
```html
<div class="bg-zinc-50 dark:bg-black">
```

### 2. Lower Grid Opacity
Make grid patterns subtler in dark mode:
```html
<!-- 8% in light, 2% in dark -->
<div class="bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px)] 
    dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px)]">
</div>
```

### 3. Glow vs Shadow
Use glows in dark mode, shadows in light mode:
```html
<div class="shadow-lg shadow-zinc-200 dark:shadow-cyan-500/10">
```

### 4. Opacity Adjustments
Reduce opacity in dark mode for effects:
```html
<!-- Light: 15%, Dark: 15% -->
<div class="bg-cyan-100 dark:bg-cyan-500/15">
```

## Testing Checklist

Before deploying theme changes:

- [ ] Test all pages in light mode
- [ ] Test all pages in dark mode
- [ ] Verify theme toggle works
- [ ] Check theme persistence (reload page)
- [ ] Test system preference detection
- [ ] Verify all text is readable (contrast)
- [ ] Check all borders are visible
- [ ] Test hover/focus states in both modes
- [ ] Verify glassmorphism works in both modes
- [ ] Check grid patterns visibility
- [ ] Test navigation active states
- [ ] Verify button styles in both modes
- [ ] Check form inputs in both modes
- [ ] Test modals/cards in both modes

## Common Issues & Solutions

### Issue: Flash of Wrong Theme
**Solution**: Initialize theme before Vue mounts
```html
<!-- In app.blade.php -->
<script>
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    }
</script>
```

### Issue: Components Not Switching
**Solution**: Ensure all color classes have `dark:` variants

### Issue: Grid Pattern Too Bright in Dark Mode
**Solution**: Use separate opacity values:
```html
<div class="opacity-100 dark:opacity-50">
```

### Issue: Poor Contrast
**Solution**: Always test with:
- Light mode: dark text on light backgrounds
- Dark mode: light text on dark backgrounds

## Performance Optimization

### 1. Preload Theme
Add to `<head>`:
```html
<script>
    (function() {
        const t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
```

### 2. Minimize Reflows
Change theme at root level only:
```js
// Good: Change at root
document.documentElement.classList.toggle('dark');

// Bad: Change individual elements
document.querySelectorAll('.component').forEach(el => {
    el.classList.toggle('dark-mode');
});
```

### 3. CSS Variables Alternative
For complex themes, consider CSS variables:
```css
:root {
    --bg-primary: #ffffff;
    --text-primary: #18181b;
}

.dark {
    --bg-primary: #000000;
    --text-primary: #ffffff;
}
```

## Accessibility

### 1. Respect User Preferences
Always check `prefers-color-scheme` if no saved preference

### 2. ARIA Labels
```html
<button aria-label="Switch to dark mode">
```

### 3. Keyboard Navigation
Ensure theme toggle is keyboard accessible

### 4. Contrast Ratios
- Light mode: Minimum 7:1 (zinc-900 on white)
- Dark mode: Minimum 15:1 (white on black)
