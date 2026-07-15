import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

path = r'C:\Users\Ryan\.gemini\antigravity-ide\brain\0fe5cfa7-0995-4b71-8eb8-c297fd35d6f5\.system_generated\logs\transcript_full.jsonl'

with open(path, 'r', encoding='utf-8') as f:
    for idx, line in enumerate(f):
        if 'public function out()' in line:
            try:
                data = json.loads(line)
                print(f"Step {data.get('step_index')} (Source: {data.get('source')}):")
                # Look in content
                content = data.get('content', '')
                if 'public function out()' in content:
                    # extract the method out()
                    start_idx = content.find('public function out()')
                    end_idx = content.find('public function', start_idx + 25)
                    if end_idx == -1:
                        end_idx = content.find('public function stock_card()', start_idx + 25)
                    if end_idx == -1:
                        end_idx = start_idx + 3000
                    print(content[start_idx:end_idx])
                    print("="*60)
            except Exception as e:
                pass
