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
    
    # Print specific sections
    sections = [
        r'4\.3\.4\.?\s+Implementasi Proses Transaksi dan Rotasi Stok',
        r'4\.4\.2\.?\s+Pengujian Parameter Optimasi Stok',
        r'4\.5\.1\.?\s+Analisis Hasil Pengujian Algoritma'
    ]
    
    for sec in sections:
        match = re.search(sec + r'(.*?)(?=4\.\d+\.\d+|4\.\d+\s+|BAB V)', content, re.IGNORECASE | re.DOTALL)
        if match:
            print(f"=== {sec} ===")
            print(match.group(1).strip()[:1500])
            print("...\n")
        else:
            print(f"=== {sec} NOT FOUND ===")
except Exception as e:
    print("Error:", e)
