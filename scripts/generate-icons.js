#!/usr/bin/env node

/**
 * Icon Generator Script for SafeTrack PWA
 *
 * Generates PNG icons from SVG source using sharp library.
 * Run: node scripts/generate-icons.js
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import sharp from 'sharp';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const svgPath = path.join(__dirname, '../resources/icons/velotrack-logo.svg');
const outputDir = path.join(__dirname, '../public/icons');

// Icon sizes to generate
const sizes = [
  { size: 48, name: 'icon-48x48.png' },
  { size: 72, name: 'icon-72x72.png' },
  { size: 96, name: 'icon-96x96.png' },
  { size: 144, name: 'icon-144x144.png' },
  { size: 192, name: 'icon-192x192.png' },
  { size: 512, name: 'icon-512x512.png' },
];

// Apple touch icon
const appleIcon = { size: 180, name: '../apple-touch-icon.png' };

async function generateIcons() {
  console.log('🎨 Generating SafeTrack PWA icons...\n');

  // Ensure output directory exists
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  // Read SVG source
  const svgBuffer = fs.readFileSync(svgPath);

  // Generate each icon size
  for (const { size, name } of sizes) {
    const outputPath = path.join(outputDir, name);

    try {
      await sharp(svgBuffer)
        .resize(size, size)
        .png()
        .toFile(outputPath);
      console.log(`✅ Generated ${name} (${size}x${size})`);
    } catch (error) {
      console.error(`❌ Failed to generate ${name}:`, error.message);
    }
  }

  // Generate Apple touch icon
  const applePath = path.join(outputDir, appleIcon.name);

  try {
    await sharp(svgBuffer)
      .resize(appleIcon.size, appleIcon.size)
      .png()
      .toFile(applePath);
    console.log(`✅ Generated apple-touch-icon.png (${appleIcon.size}x${appleIcon.size})`);
  } catch (error) {
    console.error(`❌ Failed to generate apple-touch-icon.png:`, error.message);
  }

  // Generate favicon.ico (optional, can use SVG instead)
  const faviconPath = path.join(outputDir, '../favicon.ico');

  try {
    // Create a 32x32 favicon
    await sharp(svgBuffer)
      .resize(32, 32)
      .png()
      .toFile(faviconPath.replace('.ico', '-32x32.png'));
    console.log(`✅ Generated favicon-32x32.png (32x32)`);
    console.log('\n💡 Note: Convert favicon-32x32.png to favicon.ico using an online tool if needed.');
  } catch (error) {
    console.error(`❌ Failed to generate favicon:`, error.message);
  }

  console.log('\n✨ Icon generation complete!');
  console.log('\n📋 Next steps:');
  console.log('   1. Verify icons in public/icons/ directory');
  console.log('   2. Update manifest.json if needed');
  console.log('   3. Test PWA installation on mobile devices');
}

generateIcons().catch(error => {
  console.error('💥 Icon generation failed:', error);
  process.exit(1);
});
