import xml.etree.ElementTree as ET
import os

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd-level-1.drawio'

# Read the file
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace edge styles to add jumpStyle=arc;jumpSize=15;
import re

def add_jump_style(match):
    style_str = match.group(1)
    if 'jumpStyle' not in style_str:
        if not style_str.endswith(';'):
            style_str += ';'
        style_str += 'jumpStyle=arc;jumpSize=15;'
    return f'style="{style_str}"'

# Find all mxCell elements with edge="1" and extract their style attribute
content = re.sub(r'style="([^"]*)"(?=[^>]*edge="1")', add_jump_style, content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated DFD Level 1 drawio file with line jumps.")
