# SafeTrack Design System Skill

This skill helps maintain consistent professional, modern, and tech-inspired design with full light/dark mode support across the SafeTrack application.

## What This Skill Does

Automatically applies SafeTrack design principles when:
- Creating new UI components (buttons, cards, modals, forms)
- Modifying existing layouts or pages
- Styling navigation elements
- Working with colors, typography, or icons
- Adding visual effects or animations
- Implementing theme switching (light/dark mode)
- Migrating from single-mode to dual-mode designs

## Files in This Skill

### SKILL.md (Main File)
The primary design system guide containing:
- Core design philosophy (professional, modern, tech-inspired)
- **Theme-aware color palette** (light + dark mode)
- Typography guidelines
- Icon system (SVG-based)
- Visual effects (glassmorphism, gradients, grids)
- Layout patterns with theme support
- Animation principles
- Component patterns (buttons, forms, navigation)
- Theme implementation basics
- Accessibility guidelines

**Line count**: ~338 lines (concise with progressive disclosure)

### COLOR_REFERENCE.md (Reference File)
Detailed color specifications including:
- Complete **zinc scale** (better than slate for themes) with hex values
- **Light mode and dark mode** color combinations
- Opacity patterns for both themes
- Gradient combinations (theme-aware)
- WCAG AAA contrast ratios (both modes)
- Usage examples with `dark:` variants

Read this when you need:
- Exact hex values for custom colors
- Light/dark mode color pairings
- Accessibility verification
- Detailed gradient specifications

### THEME_IMPLEMENTATION.md (Implementation Guide)
Complete guide for implementing light/dark mode:
- Tailwind dark mode configuration
- `useTheme()` composable with full code
- Theme toggle component
- Sun/Moon icon components
- Migration from slate to zinc
- Find & replace patterns
- Testing checklist
- Common issues & solutions
- Performance optimization

Read this when:
- Implementing theme switching
- Migrating existing components to support themes
- Debugging theme-related issues
- Setting up new projects

## How It Works

The agent automatically reads SKILL.md when:
- You mention "UI", "design", "style", "navigation", "button", "card", etc.
- You create or modify components in `resources/js/`
- You work with Tailwind classes
- You ask for design help

The agent reads COLOR_REFERENCE.md when:
- You need specific color values
- You're working with custom colors
- You need accessibility information

## Design Principles Summary

**Professional**: Clean, refined, proper hierarchy  
**Modern**: Glassmorphism, gradients, smooth transitions  
**Tech-Inspired**: Grid patterns, monospace fonts, cyan/blue accents  
**Theme-Aware**: Full light/dark mode with **extra dark** mode (black/zinc-950 base)

## Quick Usage Example

When you ask the agent to create a new card component, it will automatically:
1. Use **zinc palette** with `dark:` variants (e.g., `bg-white dark:bg-zinc-800`)
2. Apply **extra dark** backgrounds in dark mode (black/zinc-950)
3. Include glassmorphism effects (theme-aware)
4. Use SVG icons (not emojis)
5. Add smooth transitions (200-300ms)
6. Include proper hover/focus states for both themes
7. Use correct accent colors (cyan-600 light, cyan-400 dark)
8. Ensure WCAG AAA contrast in both modes
9. Add theme-aware shadows (shadows in light, glows in dark)

## Example: Before vs After

**Before** (without skill):
- Agent might use emojis, random colors, inconsistent spacing
- No theme support (dark mode only or light mode only)
- Slate colors without light mode consideration
- No `dark:` variants

**After** (with skill):
- Professional SVG icons
- **Zinc/cyan color palette** with full light/dark support
- **Extra dark mode** (black/zinc-950 base)
- Glassmorphism effects (theme-aware)
- Smooth transitions
- Tech-inspired grid patterns (subtler in dark mode)
- All components work in both light and dark modes
- Theme toggle included

**Example Component**:
```html
<!-- Generated with theme-aware design skill -->
<div class="rounded-lg 
    bg-white dark:bg-zinc-800/50 
    border border-zinc-200 dark:border-white/5 
    text-zinc-900 dark:text-white 
    p-6 
    shadow-lg shadow-zinc-200 dark:shadow-cyan-500/10 
    backdrop-blur-sm">
    <h3 class="text-lg font-semibold">Card Title</h3>
    <p class="text-zinc-600 dark:text-zinc-400">Description</p>
</div>
```

## Testing the Skill

Try asking the agent:
- "Create a new notification card"
- "Add a secondary button to this form"
- "Update the dashboard layout with theme support"
- "Create a new icon component"
- "Add a theme toggle button"

The agent should automatically apply SafeTrack design principles without you needing to specify the style.

## Updating This Skill

If design preferences change:
1. Update SKILL.md with new principles (keep under 500 lines)
2. Update COLOR_REFERENCE.md if colors change
3. Add examples to demonstrate new patterns
4. Use progressive disclosure - move verbose content to reference files

## Skill Metadata

- **Name**: design-system
- **Type**: Project skill (shared with team)
- **Location**: `.cursor/skills/design-system/`
- **Triggers**: UI, components, styling, design, layout, navigation, forms, buttons, cards, modals, theme, light mode, dark mode
- **Coverage**: Comprehensive (colors, typography, icons, effects, animations, light/dark themes)
- **Theme Support**: Full light/dark mode with extra dark aesthetic
- **Color System**: Zinc scale (replaces slate) for better theme support
