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
    # Skip TOC by finding the second occurrence of BAB III or looking for BAB IIIMETODE
    match = re.search(r'BAB III\s*METODE PENELITIAN(.*?)(?=BAB IV\s*HASIL)', content, re.IGNORECASE | re.DOTALL)
    if match:
        print(match.group(1).strip()[:5000])
    else:
        print("BAB III body not found.")
except Exception as e:
    print("Error:", e)
