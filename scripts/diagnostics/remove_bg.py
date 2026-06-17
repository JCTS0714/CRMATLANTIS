from PIL import Image
import os

input_path = os.path.join('public','images','logo_alta_calidad.png')
output_path = os.path.join('public','images','logo_alta_calidad_transparent.png')

if not os.path.exists(input_path):
    print('Input file not found:', input_path)
    raise SystemExit(1)

im = Image.open(input_path).convert('RGBA')
px = im.load()
width, height = im.size

# Thresholds: pixels brighter than this will be made transparent
brightness_threshold = 240

for y in range(height):
    for x in range(width):
        r,g,b,a = px[x,y]
        if r >= brightness_threshold and g >= brightness_threshold and b >= brightness_threshold:
            px[x,y] = (255,255,255,0)

im.save(output_path, 'PNG')
print('Saved:', output_path)
