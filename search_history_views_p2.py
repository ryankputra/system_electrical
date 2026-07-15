import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

path = r'C:\Users\Ryan\.gemini\antigravity-ide\brain\0fe5cfa7-0995-4b71-8eb8-c297fd35d6f5\.system_generated\logs\transcript_full.jsonl'

with open(path, 'r', encoding='utf-8') as f:
    for idx, line in enumerate(f):
        if 'application/controllers/History.php' in line:
            try:
                data = json.loads(line)
                if data.get('source') == 'MODEL' and data.get('type') == 'VIEW_FILE':
                    args = data.get('tool_calls', [{}])[0].get('arguments', {})
                    print(f"Step {data.get('step_index')} | StartLine: {args.get('StartLine')} | EndLine: {args.get('EndLine')}")
            except Exception as e:
                pass
