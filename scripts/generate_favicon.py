#!/usr/bin/env python3
"""Generate favicon with trophy icon for contest platform."""

from PIL import Image, ImageDraw, ImageFont
import os

def create_trophy_favicon(size, output_path):
    """Create a trophy-themed favicon."""
    img = Image.new('RGBA', (size, size), (255, 255, 255, 0))
    draw = ImageDraw.Draw(img)
    
    # Scale factor
    s = size / 64
    
    # Colors
    gold1 = (255, 215, 0)      # #FFD700
    gold2 = (255, 193, 7)      # #FFC107
    gold3 = (255, 143, 0)      # #FF8F00
    gold_dark = (230, 168, 0)  # #E6A800
    star = (255, 248, 225)     # #FFF8E1
    
    cx, cy = size // 2, size // 2
    
    # Cup body (simplified trophy shape)
    cup_top = int(cy - 20 * s)
    cup_bottom = int(cy + 10 * s)
    cup_width = int(22 * s)
    
    # Draw cup body
    draw.polygon([
        (cx - cup_width, cup_top),
        (cx + cup_width, cup_top),
        (cx + cup_width * 0.7, cup_bottom),
        (cx - cup_width * 0.7, cup_bottom),
    ], fill=gold1, outline=gold_dark)
    
    # Cup rim (ellipse)
    draw.ellipse([
        (cx - cup_width, cup_top - int(3*s)),
        (cx + cup_width, cup_top + int(3*s))
    ], fill=gold1, outline=gold_dark)
    
    # Stem
    stem_w = int(3 * s)
    draw.rectangle([
        (cx - stem_w, cup_bottom),
        (cx + stem_w, cup_bottom + int(10 * s))
    ], fill=gold_dark)
    
    # Base
    base_w = int(10 * s)
    base_h = int(3 * s)
    draw.rounded_rectangle([
        (cx - base_w, cup_bottom + int(10 * s)),
        (cx + base_w, cup_bottom + int(10 * s) + base_h)
    ], radius=int(2 * s), fill=gold_dark)
    
    # Handles (simple curves)
    handle_w = int(8 * s)
    # Left handle
    draw.arc([
        (cx - cup_width - handle_w, cup_top + int(2*s)),
        (cx - cup_width, cup_top + int(12*s))
    ], 0, 180, fill=gold2, width=int(3*s))
    # Right handle
    draw.arc([
        (cx + cup_width, cup_top + int(2*s)),
        (cx + cup_width + handle_w, cup_top + int(12*s))
    ], 180, 360, fill=gold2, width=int(3*s))
    
    # Star in center
    star_size = int(6 * s)
    draw.polygon([
        (cx, cup_top + star_size),
        (cx - int(2*s), cup_top + int(4*s)),
        (cx - star_size, cup_top + int(2*s)),
        (cx - int(2*s), cup_top),
        (cx, cup_top - int(2*s)),
        (cx + int(2*s), cup_top),
        (cx + star_size, cup_top + int(2*s)),
        (cx + int(2*s), cup_top + int(4*s)),
    ], fill=star)
    
    img.save(output_path)

# Generate at different sizes
sizes = {
    'favicon.ico': 32,       # Will be used as ICO
    'favicon-32x32.png': 32,
    'favicon-16x16.png': 16,
    'apple-touch-icon.png': 180,
}

output_dir = '/home/toatall/repos/app-contest-platform/public'

for filename, size in sizes.items():
    path = os.path.join(output_dir, filename)
    create_trophy_favicon(size, path)
    print(f"Created {filename} ({size}x{size})")

# Create ICO from 32x32 PNG
from PIL import Image
img_32 = Image.open(os.path.join(output_dir, 'favicon-32x32.png'))
img_32.save(os.path.join(output_dir, 'favicon.ico'), format='ICO', sizes=[(32, 32)])
print("Created favicon.ico")

print("\nAll favicons generated successfully!")
