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
    
    # print text starting from 4.3.4 (skip the first one which is TOC)
    matches = list(re.finditer(r'4\.3\.4\..*?\n', content))
    if len(matches) > 1:
        start_idx = matches[1].start()
        print("=== 4.3.4 BODY ===")
        print(content[start_idx:start_idx+1500])
        print("-\n")
        
    matches2 = list(re.finditer(r'4\.4\.2\..*?\n', content))
    if len(matches2) > 1:
        start_idx = matches2[1].start()
        print("=== 4.4.2 BODY ===")
        print(content[start_idx:start_idx+1500])
        print("-\n")
except Exception as e:
    print("Error:", e)
