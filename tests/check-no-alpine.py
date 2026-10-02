import os
import re
import sys

# Directory to scan
TARGET_DIR = "plugins/bankai-core"

# Forbidden patterns
FORBIDDEN_PATTERNS = [
    r"x-data",
    r"x-show",
    r"x-text",
    r"x-html",
    r"x-cloak",
    r"x-transition",
    r"x-bind",
    r"x-on",
    r"x-model",
    r"x-for",
    r"x-if",
    r"x-ref",
    r":class=",
    r":style=",
    r":dir=",
    r"@click=",
    r"@input=",
    r"hx-",
    r"Alpine",
    r"htmx"
]

compiled_patterns = [re.compile(p, re.IGNORECASE) for p in FORBIDDEN_PATTERNS]

violations = []

for root, dirs, files in os.walk(TARGET_DIR):
    for file in files:
        if file.endswith((".php", ".js", ".css")):
            filepath = os.path.join(root, file)
            with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                for line_num, line in enumerate(f, start=1):
                    for pattern, raw_p in zip(compiled_patterns, FORBIDDEN_PATTERNS):
                        if pattern.search(line):
                            # Skip comments or non-code docs if necessary
                            violations.append((filepath, line_num, raw_p, line.strip()))

if violations:
    print(f"FAILED: Found {len(violations)} forbidden Alpine/HTMX directives/references:")
    for v in violations:
        print(f"  {v[0]}:{v[1]} -> matched '{v[2]}' in: {v[3]}")
    sys.exit(1)
else:
    print("SUCCESS: Zero Alpine/HTMX directives or references found across bankai-core!")
    sys.exit(0)
