import pypdf

pdf_path = r'C:\Users\Ryan\Documents\TA\ai\skrispi kampus lain.pdf'
reader = pypdf.PdfReader(pdf_path)

print("--- PAGE 70 ---")
print(reader.pages[69].extract_text()[:2000])

print("\n--- PAGE 71 ---")
print(reader.pages[70].extract_text()[:2000])

print("\n--- PAGE 72 ---")
print(reader.pages[71].extract_text()[:2000])
