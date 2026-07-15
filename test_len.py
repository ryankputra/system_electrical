import zipfile
import xml.etree.ElementTree as ET

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
print("Length of content:", len(content))
# Find occurrences of 'Login'
import re
print("Occurrences of Login:", len(re.findall(r'Login', content)))
# Print first 2000 chars of actual content (skip TOC by searching for BAB I PENDAHULUAN body)
print(content[15000:17000])
