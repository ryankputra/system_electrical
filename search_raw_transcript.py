path = r'C:\Users\Ryan\.gemini\antigravity-ide\brain\0fe5cfa7-0995-4b71-8eb8-c297fd35d6f5\.system_generated\logs\transcript_full.jsonl'

found = 0
with open(path, 'r', encoding='utf-8') as f:
    for idx, line in enumerate(f):
        if 'history/mine' in line.lower() or 'print_sticker' in line.lower():
            print(f"Line {idx} matches:")
            print(line[:1000]) # print first 1000 chars of matching line
            found += 1
            if found >= 10:
                break
