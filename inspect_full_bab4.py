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
    # Find BAB IV and extract the full text
    match = re.search(r'BAB IV\s*HASIL(.*?)(?=BAB V)', content, re.IGNORECASE | re.DOTALL)
    if match:
        bab4_content = match.group(1).strip()
        with open('bab4_full_inspect.txt', 'w', encoding='utf-8') as f:
            f.write(bab4_content)
        print("Successfully extracted BAB IV content. Total length:", len(bab4_content))
    else:
        print("BAB IV not found in body.")
except Exception as e:
    print("Error:", e)
