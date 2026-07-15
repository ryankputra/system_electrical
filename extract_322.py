import zipfile
import xml.etree.ElementTree as ET
import re

path = r'C:\Users\Ryan\Documents\TA\ai\TA.Ayusiawan(rev5).docx'

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
    # Extract text under 3.2.2 until the next heading
    match = re.search(r'(3\.2\.2\.?\s+Implementasi Algoritma Antrean.*?)(?=3\.3\.)', content, re.IGNORECASE | re.DOTALL)
    if match:
        print(match.group(1).strip())
    else:
        print("Section 3.2.2 not found!")
except Exception as e:
    print("Error:", e)
