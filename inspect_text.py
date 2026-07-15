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
    
    # print occurrences of '4.3.4'
    for m in re.finditer(r'4\.3\.4', content):
        start = max(0, m.start() - 50)
        end = min(len(content), m.end() + 1000)
        print("MATCH 4.3.4:")
        print(content[start:end])
        print("="*40)
        
    for m in re.finditer(r'4\.4\.2', content):
        start = max(0, m.start() - 50)
        end = min(len(content), m.end() + 1000)
        print("MATCH 4.4.2:")
        print(content[start:end])
        print("="*40)
except Exception as e:
    print("Error:", e)
