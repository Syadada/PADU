import re

file_path = r"C:\Users\rasyaad\.gemini\antigravity-ide\brain\30726a87-ac73-4ada-8a51-51616570b1a3\.system_generated\steps\111\content.md"

with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
    content = f.read()

# Find long text sequences
text_blocks = re.findall(r'[A-Za-z0-9_ \-\.:,\?\!\(\)\n\r]{30,}', content)

print(f"Total blocks found: {len(text_blocks)}")
for b in text_blocks:
    if any(w in b.lower() for w in ["tahap", "swimlane", "flowchart", "wajah", "kamera", "lock", "micro", "table"]):
        print("FOUND BLOCK:")
        print(b[:500])
        print("="*40)
