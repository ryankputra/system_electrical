import xml.etree.ElementTree as ET

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd-level-1.drawio'

tree = ET.parse(path)
root = tree.getroot()

changed = 0
for cell in root.iter('mxCell'):
    if cell.get('edge') == '1':
        style = cell.get('style', '')
        if 'jumpSize=15' in style:
            style = style.replace('jumpSize=15', 'jumpSize=8')
            cell.set('style', style)
            changed += 1

if changed > 0:
    tree.write(path, encoding='utf-8', xml_declaration=False)
    print(f"Updated {changed} edges to jumpSize=8.")
else:
    print("No edges needed updating.")
