# SpeedoMontor PWA Testing Guide

This guide provides instructions for testing the PWA manifest implementation (US-5.5).

## Prerequisites

- SpeedoMontor running on a publicly accessible HTTPS URL (required for PWA)
- Android device with Chrome browser (for Android testing)
- iOS device with Safari (for iOS testing)
- Desktop Chrome browser with DevTools (for initial verification)

---

## Phase 1: Desktop Verification (Chrome DevTools)

### 1.1 Manifest Inspection

1. Open SpeedoMontor in Chrome desktop browser
2. Open DevTools (F12) → Application tab
3. Navigate to "Manifest" section in left sidebar
4. Verify the following fields:

**Expected Values:**
- **Name:** SpeedoMontor - Speed Monitoring System
- **Short Name:** SpeedoMontor
- **Start URL:** /
- **Theme Color:** #06b6d4 (cyan)
- **Background Color:** #0a0c0f (dark)
- **Display:** standalone
- **Orientation:** portrait-primary

**Icon Verification:**
- 6 icons listed (48x48, 72x72, 96x96, 144x144, 192x192, 512x512)
- All icons load without errors (no red warnings)
- Click each icon to preview

### 1.2 Service Worker Verification

1. In DevTools → Application → Service Workers
2. Verify Service Worker is registered
3. Check "Update on reload" is enabled for testing
4. Click "Update" to fetch latest version

### 1.3 Quick Install Test (Desktop)

1. In DevTools → Application → Manifest
2. Click "Add to home screen" link at bottom
3. Desktop shortcut should be created
4. Launch from shortcut → should open in standalone window (no browser UI)

**Checklist:**
- [ ] Manifest loads without errors
- [ ] All fields display correct values
- [ ] All 6 icons preview correctly
- [ ] Service Worker is active
- [ ] Desktop install creates shortcut
- [ ] Shortcut launches in standalone mode

---

## Phase 2: Android Testing (Chrome for Android)

### 2.1 Manifest & Installation

1. Open SpeedoMontor in Chrome for Android
2. Wait 3-5 seconds for install prompt (or check Chrome menu → "Install app")
3. Native "Add to Home Screen" banner should appear

**If install prompt doesn't appear:**
- Check Chrome menu (⋮) → "Add to Home Screen" option
- Ensure you're on HTTPS (PWA requires secure connection)

4. Tap "Install" / "Add to Home Screen"
5. Verify icon preview shows SpeedoMontor car icon with gradient
6. Confirm installation

### 2.2 Home Screen Verification

1. Go to Android home screen
2. Locate "SpeedoMontor" app icon
3. Verify icon displays correctly (car icon on cyan-blue gradient)
4. Long-press icon → App info

**Expected:**
- App name: "SpeedoMontor"
- Icon is clear and recognizable
- No default PWA placeholder icon

### 2.3 Standalone Mode Testing

1. Launch SpeedoMontor from home screen icon
2. App should open in standalone mode (full-screen, no browser UI)
3. Verify status bar color matches theme (cyan #06b6d4)
4. Check no Chrome address bar at top
5. Check no browser navigation buttons at bottom

### 2.4 Functional Testing

1. Navigate through app (Dashboard → Speedometer → My Trips)
2. Verify all routes work correctly
3. Test offline mode (enable Airplane mode)
4. Verify offline indicator appears
5. Re-enable internet, verify sync works

**Android Checklist:**
- [ ] Install prompt appears (or Install option in menu)
- [ ] Icon preview is correct during installation
- [ ] App icon appears on home screen
- [ ] Icon is clear and recognizable
- [ ] Standalone mode (no browser UI)
- [ ] Status bar color is cyan (#06b6d4)
- [ ] All navigation works
- [ ] Offline mode functions correctly

---

## Phase 3: iOS Testing (Safari on iPhone/iPad)

### 3.1 Installation Process

**Note:** iOS does not show automatic install prompts. Users must manually add to home screen.

1. Open SpeedoMontor in Safari (iOS 15+)
2. Tap Share button (box with arrow pointing up)
3. Scroll down and tap "Add to Home Screen"
4. Verify icon preview shows SpeedoMontor car icon
5. Edit name if desired (default: "SpeedoMontor")
6. Tap "Add"

### 3.2 Home Screen Verification

1. Go to iOS home screen
2. Locate "SpeedoMontor" app icon
3. Verify icon displays correctly (should use apple-touch-icon.png - 180x180)
4. Icon should be sharp and clear (no blur)

### 3.3 Standalone Mode Testing

1. Launch SpeedoMontor from home screen icon
2. App should open in standalone mode (no Safari UI)
3. Verify status bar style (should be black-translucent, blending with dark theme)
4. Check no Safari toolbar at bottom
5. Check no address bar at top

### 3.4 Viewport & Safe Areas

1. On iPhone with notch (iPhone X and newer), verify content respects safe areas
2. No content should be obscured by notch or home indicator
3. Navigation should not overlap with safe areas

**iOS Checklist:**
- [ ] Share → "Add to Home Screen" option available
- [ ] Icon preview is correct
- [ ] App icon appears on home screen
- [ ] Icon is sharp and clear (180x180)
- [ ] Standalone mode (no Safari UI)
- [ ] Status bar blends with dark theme
- [ ] Safe areas respected (notch, home indicator)
- [ ] All navigation works
- [ ] Offline mode functions correctly

---

## Phase 4: Lighthouse PWA Audit

### 4.1 Run Lighthouse Audit

**Desktop Chrome:**
1. Open SpeedoMontor in Chrome
2. Open DevTools (F12) → Lighthouse tab
3. Select "Progressive Web App" category
4. Select "Mobile" device
5. Click "Analyze page load"

**CLI (Alternative):**
```bash
npx lighthouse https://your-SpeedoMontor-url.com --view --preset=pwa
```

### 4.2 Expected Scores

**Target Scores:**
- **PWA Optimized:** ≥ 90/100 ✅
- **Installable:** Pass ✅
- **PWA Optimized:** Pass ✅

**Key Checks:**
- ✅ Registers a service worker
- ✅ Responds with a 200 when offline
- ✅ Has a valid web app manifest
- ✅ Configured for a custom splash screen
- ✅ Sets a theme color for the address bar
- ✅ Content is sized correctly for the viewport
- ✅ Has a maskable icon

### 4.3 Common Issues & Fixes

**Issue:** "Manifest doesn't have a maskable icon"
- **Fix:** Update manifest.json, add `"purpose": "maskable"` to 192x192 and 512x512 icons (already done ✅)

**Issue:** "Start URL doesn't respond with a 200 when offline"
- **Fix:** Verify Service Worker caches `/` in PRECACHE_URLS (already done ✅)

**Issue:** "Theme color not set"
- **Fix:** Add `<meta name="theme-color">` to app.blade.php (already done ✅)

**Issue:** Icons return 404
- **Fix:** Verify icons exist in `public/icons/` directory
- Run: `ls -la public/icons/` to check

**Lighthouse Checklist:**
- [ ] PWA score ≥ 90/100
- [ ] All "Installable" checks pass
- [ ] All "PWA Optimized" checks pass
- [ ] No critical errors in report
- [ ] Maskable icon configured
- [ ] Service Worker registered
- [ ] Offline fallback working

---

## Phase 5: Cross-Browser Testing

### 5.1 Samsung Internet (Android)

**Why test:** Samsung Internet has significant market share in Indonesia

1. Open SpeedoMontor in Samsung Internet
2. Tap menu → "Add page to" → "Home screen"
3. Verify installation works
4. Test standalone mode

### 5.2 Edge Mobile (Android)

1. Open SpeedoMontor in Edge for Android
2. Check install prompt appears
3. Test installation flow

### 5.3 Firefox Mobile (Android)

**Note:** Firefox Android has limited PWA support (no standalone mode)

1. Open SpeedoMontor in Firefox Android
2. Verify manifest loads (may not support full PWA features)
3. Test "Add to Home Screen" if available

**Cross-Browser Checklist:**
- [ ] Samsung Internet install works
- [ ] Samsung Internet standalone mode works
- [ ] Edge mobile install works
- [ ] Firefox basic functionality works

---

## Phase 6: Regression Testing

Verify existing features still work after PWA changes:

### 6.1 Service Worker (US-5.4)

- [ ] Service Worker still registers on first load
- [ ] Update notification appears when new SW version available
- [ ] Offline mode indicator works
- [ ] Cached assets load from cache

### 6.2 Background Sync (US-5.3)

- [ ] Background sync triggers when online
- [ ] Sync progress indicator updates correctly
- [ ] Failed syncs retry automatically

### 6.3 Offline Storage (US-5.1-5.2)

- [ ] Trips save to IndexedDB when offline
- [ ] Speed logs stored correctly
- [ ] Data syncs when connection restored

### 6.4 Core Functionality

- [ ] Speedometer displays GPS speed
- [ ] Trip start/stop works
- [ ] Trip history loads correctly
- [ ] Statistics page displays charts
- [ ] Profile/settings accessible

---

## Troubleshooting

### Manifest Not Loading

**Symptoms:** DevTools shows "No manifest detected"

**Solutions:**
1. Check HTTPS (manifest requires secure origin)
2. Verify `<link rel="manifest">` in app.blade.php
3. Check manifest.json is accessible: `curl https://your-url/manifest.json`
4. Clear browser cache and reload

### Icons Not Displaying

**Symptoms:** Default browser icon shown instead of SpeedoMontor icon

**Solutions:**
1. Verify icons exist: `ls -la public/icons/`
2. Check icon URLs in manifest.json match actual files
3. Clear browser cache
4. For iOS: Add `?v=2` to apple-touch-icon URL to bust cache

### Install Prompt Not Appearing (Android)

**Symptoms:** No "Add to Home Screen" banner on Android Chrome

**Possible Causes:**
1. User previously dismissed prompt (Chrome remembers for 90 days)
2. PWA criteria not met (check Lighthouse audit)
3. App already installed

**Solutions:**
1. Check Chrome menu → "Add to Home Screen" (manual trigger)
2. Run Lighthouse audit to verify PWA criteria
3. Uninstall existing app and retry
4. Clear Chrome data for site

### Standalone Mode Not Working (iOS)

**Symptoms:** Safari UI visible when launching from home screen

**Solutions:**
1. Verify `<meta name="apple-mobile-web-app-capable" content="yes">` in app.blade.php
2. Re-install app (remove from home screen, add again)
3. Check iOS version (requires iOS 11.3+)

---

## Success Criteria Summary

All US-5.5 acceptance criteria must be met:

✅ **`public/manifest.json` created** - Complete, accessible at `/manifest.json`  
✅ **App name, short name configured** - "SpeedoMontor - Speed Monitoring System" / "SpeedoMontor"  
✅ **Icons for all sizes** - 48, 72, 96, 144, 192, 512 + apple-touch-icon (180)  
✅ **Theme color, background color set** - #06b6d4 (cyan), #0a0c0f (dark)  
✅ **Display: standalone** - Configured in manifest.json  
✅ **Start URL configured** - `/` (redirects to role-based dashboard)  
✅ **Installable on iOS and Android** - Requires physical device testing ⚠️

---

## Next Steps After Testing

Once all tests pass:

1. **Update US-5.5 Implementation Summary** with:
   - Lighthouse scores
   - Screenshots of installed app (Android/iOS)
   - Any issues encountered and fixes applied

2. **Proceed to US-5.6**: Sync Queue Management UI
   - Display pending sync items count
   - Manual "Sync Now" button
   - Last sync timestamp

3. **Optional Enhancements** (Backlog):
   - Custom install prompt component (InstallPrompt.vue)
   - App shortcuts (Quick Actions)
   - Share Target API

---

## Testing Evidence

Document testing results with screenshots:

**Required Screenshots:**
1. Chrome DevTools Manifest tab (desktop)
2. Lighthouse PWA audit results
3. Android home screen with SpeedoMontor icon
4. Android standalone mode (no browser UI)
5. iOS home screen with SpeedoMontor icon
6. iOS standalone mode (no Safari UI)

**Save to:** `docs/screenshots/us-5.5/`

---

**Last Updated:** April 4, 2026  
**Related User Stories:** US-5.4 (Service Worker), US-5.1-5.3 (Offline Storage)  
**Developer:** Zulfikar Hidayatullah
