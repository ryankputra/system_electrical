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
    # Print the chapters / headers to see the structure
    headers = re.findall(r'^(BAB [A-Z]+|^\d+\.\d+.*|^\d+\.\d+\.\d+.*)', content, re.MULTILINE)
    print("Found Headers:")
    for h in headers:
        print(h)
except Exception as e:
    print("Error:", e)
