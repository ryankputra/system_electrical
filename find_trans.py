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

content = get_docx_text(path)

# find "Implementasi Proses Transaksi"
for m in re.finditer(r'Implementasi Proses Transaksi', content, re.IGNORECASE):
    print("Match index:", m.start())
    print(content[m.start()-50:m.start()+800])
    print("="*40)
