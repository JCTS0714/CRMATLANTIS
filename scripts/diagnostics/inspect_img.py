from PIL import Image
from collections import Counter
p = 'public/images/logo_alta_calidad_transparent.png'
im = Image.open(p)
print('Path:',p)
print('Mode:',im.mode)
print('Size:',im.size)
px = im.convert('RGBA').load()
w,h = im.size
cx,cy = w//2,h//2
print('Center pixel RGBA:', px[cx,cy])
# alpha stats
im_rgba = im.convert('RGBA')
alpha = im_rgba.split()[-1]
alpha_vals = alpha.getextrema()
print('Alpha extrema (min,max):', alpha_vals)
# Count colors (top 5)
c = Counter()
for y in range(h):
    for x in range(w):
        c[px[x,y]] += 1
print('Top 5 colors:', c.most_common(5))
# bbox of non-transparent
bbox = alpha.getbbox()
print('Alpha bbox:', bbox)
