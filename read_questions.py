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
    
    # find occurrences of 'Pertanyaan Penelitian'
    matches = list(re.finditer(r'Pertanyaan Penelitian', content, re.IGNORECASE))
    for i, m in enumerate(matches):
        start = m.start()
        end = min(len(content), m.end() + 1500)
        print(f"MATCH {i}:")
        print(content[start:end])
        print("="*40)
except Exception as e:
    print("Error:", e)
