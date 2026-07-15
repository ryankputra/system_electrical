import zipfile
import xml.etree.ElementTree as ET
import re

path = r'C:\Users\Ryan\Documents\TA\ai\TA.Ayusiawan(rev6).docx'

def get_docx_text(path):
    text = []
    with zipfile.ZipFile(path) as docx:
        tree = ET.XML(docx.read('word/document.xml'))
        for paragraph in tree.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p'):
            para_text = "".join(node.text for node in paragraph.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t') if node.text)
            if para_text:
                text.append(para_text)
    return "\n".join(text)

try:
    content = get_docx_text(path)
    # Find all matches of BAB IV ... BAB V
    matches = list(re.finditer(r'BAB IV\s*HASIL(.*?)(?=BAB V)', content, re.IGNORECASE | re.DOTALL))
    print(f"Found {len(matches)} matches.")
    for i, m in enumerate(matches):
        txt = m.group(1).strip()
        print(f"Match {i} length: {len(txt)}")
        if len(txt) > 2000:
            with open('bab4_actual_body.txt', 'w', encoding='utf-8') as f:
                f.write(txt)
            print(f"Saved Match {i} as bab4_actual_body.txt")
except Exception as e:
    print("Error:", e)
