import zipfile
import xml.etree.ElementTree as ET
import re

path = r'C:\Users\Ryan\Documents\TA\ai\TA.Ayusiawan(rev6).docx'
ns = {
    'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
}

def get_clean_text(elem):
    texts = elem.findall('.//w:t', ns)
    return "".join(t.text for t in texts if t.text)

def docx_to_html_body(path):
    html_blocks = []
    with zipfile.ZipFile(path) as docx:
        tree = ET.XML(docx.read('word/document.xml'))
        body = tree.find('w:body', ns)
        
        for child in body:
            tag = child.tag.split('}')[-1]
            if tag == 'p':
                text = get_clean_text(child).strip()
                if text:
                    html_blocks.append(('p', text))
            elif tag == 'tbl':
                rows = []
                for row_elem in child.findall('w:tr', ns):
                    row_data = []
                    for cell_elem in row_elem.findall('w:tc', ns):
                        # Get all text inside cell paragraphs
                        cell_paras = cell_elem.findall('w:p', ns)
                        cell_text = " ".join(get_clean_text(p).strip() for p in cell_paras if get_clean_text(p).strip())
                        row_data.append(cell_text)
                    rows.append(row_data)
                html_blocks.append(('table', rows))
    return html_blocks

try:
    blocks = docx_to_html_body(path)
    print(f"Extracted {len(blocks)} blocks from DOCX.")
    
    # Let's save a debug view of the blocks to find BAB IV
    with open('docx_blocks.txt', 'w', encoding='utf-8') as f:
        for idx, (btype, content) in enumerate(blocks):
            f.write(f"BLOCK {idx} [{btype}]: {str(content)[:100]}\n")
    print("Debug blocks saved.")
except Exception as e:
    print("Error:", e)
