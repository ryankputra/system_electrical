import re

html_path = r'C:\Users\Ryan\Documents\TA\diagrams\Revisi_Lengkap_BAB_IV.html'

with open(html_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace the <pre>...</pre> block with a screenshot placeholder and the caption "Gambar 4.27 Kode Program Algoritma FIFO"
pattern_pre = re.compile(r'<pre style="background: #f8f9fa;.*?</pre>', re.DOTALL)

placeholder_html = """
<div style="border: 2px dashed #999; padding: 20px; text-align: center; background: #fafafa; margin: 15px 0; font-style: italic;">
    [Tempatkan Screenshot Kode Program Algoritma FIFO (History_model.php Baris 174-213) di sini]
</div>
<p style="color: gray; font-style: italic; text-align: center;">Gambar 4.27 Kode Program Algoritma FIFO</p>
"""

# Re-read the HTML to make sure we match it. We can do it by simple string replace or regex.
# Let's inspect the file content around the pre block to match perfectly.
# In the HTML:
# <pre style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; font-family: 'Consolas', 'Courier New', monospace; font-size: 10pt; line-height: 1.4; margin-left: 0.5in; margin-right: 0.5in; overflow-x: auto; text-align: left; white-space: pre-wrap; word-wrap: break-word;">
# ...
# </pre>

content_replaced = pattern_pre.sub(placeholder_html, content)

# 2. Shift all image numbers (and their references in paragraphs) from Gambar 4.27 to Gambar 4.33 by 1.
# Since we inserted Gambar 4.27, the old Gambar 4.27 becomes 4.28, etc.
# Let's do a loop starting from the largest index down to the smallest (from 33 down to 27) to avoid double shifting.

for num in range(33, 26, -1):
    old_cap = f"Gambar 4.{num}"
    new_cap = f"Gambar 4.{num+1}"
    content_replaced = content_replaced.replace(old_cap, new_cap)
    
    old_cap_lc = f"gambar 4.{num}"
    new_cap_lc = f"gambar 4.{num+1}"
    content_replaced = content_replaced.replace(old_cap_lc, new_cap_lc)

# Write it back to the file
with open(html_path, 'w', encoding='utf-8') as f:
    f.write(content_replaced)

print("Shifted and replaced with placeholder successfully.")
