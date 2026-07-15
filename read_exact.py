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
    # find occurrences of "4.3.4" and print the following text
    matches = re.finditer(r'(4\.3\.4\..*?)(?=4\.3\.5|4\.4)', content, re.IGNORECASE | re.DOTALL)
    for i, m in enumerate(matches):
        print(f"Match {i} (4.3.4):")
        print(m.group(1).strip()[:1000])
        print("-\n")
        
    matches = re.finditer(r'(4\.4\.2\..*?)(?=4\.5|4\.4\.3)', content, re.IGNORECASE | re.DOTALL)
    for i, m in enumerate(matches):
        print(f"Match {i} (4.4.2):")
        print(m.group(1).strip()[:1000])
        print("-\n")
        
    matches = re.finditer(r'(4\.5\.1\..*?)(?=4\.5\.2|4\.6)', content, re.IGNORECASE | re.DOTALL)
    for i, m in enumerate(matches):
        print(f"Match {i} (4.5.1):")
        print(m.group(1).strip()[:1000])
        print("-\n")
except Exception as e:
    print("Error:", e)
