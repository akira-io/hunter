#!/bin/bash

# PWA Icon Generator Script
# Usage: ./generate-icons.sh path/to/your/logo.png

INPUT_IMAGE="$1"
OUTPUT_DIR="public/icons"

if [ -z "$INPUT_IMAGE" ]; then
    echo "Error: Please provide the path to your logo image"
    echo "Usage: ./generate-icons.sh path/to/logo.png"
    exit 1
fi

if [ ! -f "$INPUT_IMAGE" ]; then
    echo "Error: File '$INPUT_IMAGE' not found"
    exit 1
fi

# Check if ImageMagick is installed
if ! command -v convert &> /dev/null; then
    echo "Error: ImageMagick is not installed"
    echo "Install it with:"
    echo "  - macOS: brew install imagemagick"
    echo "  - Ubuntu: sudo apt-get install imagemagick"
    echo "  - Or use online tool: https://www.pwabuilder.com/imageGenerator"
    exit 1
fi

# Create icons directory
mkdir -p "$OUTPUT_DIR"

# Array of icon sizes
SIZES=(72 96 128 144 152 192 384 512)

echo "Generating PWA icons..."

for SIZE in "${SIZES[@]}"; do
    OUTPUT_FILE="$OUTPUT_DIR/icon-${SIZE}x${SIZE}.png"
    echo "Creating ${SIZE}x${SIZE} icon..."

    convert "$INPUT_IMAGE" \
        -resize ${SIZE}x${SIZE} \
        -background none \
        -gravity center \
        -extent ${SIZE}x${SIZE} \
        "$OUTPUT_FILE"

    if [ $? -eq 0 ]; then
        echo "✓ Created: $OUTPUT_FILE"
    else
        echo "✗ Failed to create: $OUTPUT_FILE"
    fi
done

echo ""
echo "✅ Icon generation complete!"
echo "Icons saved to: $OUTPUT_DIR/"
echo ""
echo "Generated icons:"
ls -lh "$OUTPUT_DIR"
