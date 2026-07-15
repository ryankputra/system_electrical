import xml.etree.ElementTree as ET

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd-level-1.drawio'

tree = ET.parse(path)
root = tree.getroot()

changed = 0
for cell in root.iter('mxCell'):
    if cell.get('edge') == '1':
        style = cell.get('style', '')
        if 'jumpStyle' not in style:
            if style and not style.endswith(';'):
                style += ';'
            style += 'jumpStyle=arc;jumpSize=15;'
            cell.set('style', style)
            changed += 1

if changed > 0:
    tree.write(path, encoding='utf-8', xml_declaration=False)
    print(f"Updated {changed} edges with line jumps.")
else:
    print("No edges needed updating.")
