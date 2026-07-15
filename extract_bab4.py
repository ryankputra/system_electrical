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
    # Find BAB IV and extract headers
    match = re.search(r'BAB IV\s*HASIL(.*?)(?=BAB V)', content, re.IGNORECASE | re.DOTALL)
    if match:
        bab4_text = match.group(1)
        headers = re.findall(r'^(4\.\d+\..*|4\.\d+\.\d+.*)', bab4_text, re.MULTILINE)
        print("BAB IV Headers:")
        for h in headers:
            print(h)
    else:
        print("BAB IV not found.")
except Exception as e:
    print("Error:", e)
