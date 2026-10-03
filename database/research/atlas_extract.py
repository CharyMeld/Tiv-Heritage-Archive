"""
Extract the entries of Blench's Atlas of Nigerian Languages (2020 PDF) that name a given state.

Usage: python3 atlas_extract.py <atlas.pdf> "<State name>" <out.txt> [first_page last_page]

The Atlas is printed in two columns, which a plain text extraction interleaves. Each page is cut
into its left and right halves (pdftotext -x/-W crop), read left column then right column, and the
stream is split at head entries ("142. Galambu"). A head is recognised by its number following the
previous head's (within +3), and by not looking like a numbered field (fields 3–17 start with a state
name, a figure or a family name). Every entry whose text contains the state name is written out,
separated by '=====' (the same format as data/atlas_nasarawa_blocks.txt).

Blocks can still carry fragments of the neighbouring entry at a column break: every block must be
read by hand before use.
"""
import re, subprocess, sys

FIELD_START = ("Benue", "Chadic", "Adamawa", "Atlantic", "Niger", "Nilo", "Mande", "Afro", "Ubangi", "Kainji", "Plateau", "Bantu", "Volta",
               "Cross", "Delta", "Ijoid", "Edoid", "Idomoid", "Igboid", "Yoruboid", "Nupoid", "Jukunoid", "Unclassified", "Isolate", "Sign",
               "Semitic", "Saharan", "Songhay", "Gur", "Kwa", "Defoid", "Akpes", "Ukaan", "Oko", "Tivoid", "Mambiloid", "Dakoid", "Bantoid")


def column(pdf, page, left):
    x = 0 if left else 298
    return subprocess.run(["pdftotext", "-f", str(page), "-l", str(page), "-x", str(x), "-y", "0", "-W", "298", "-H", "842", "-layout", pdf, "-"],
                          capture_output=True, text=True).stdout


def main():
    pdf, state, out = sys.argv[1:4]
    first, last = (int(sys.argv[4]), int(sys.argv[5])) if len(sys.argv) > 5 else (1, 198)
    lines = []
    for p in range(first, last + 1):
        for left in (True, False):
            lines += [l.strip() for l in column(pdf, p, left).split("\n")]
    entries, cur, last_head = [], [], 0
    for ln in lines:
        m = re.match(r"^(\d{1,3})\.\s+(\S.*)$", ln)
        if m:
            n, rest = int(m.group(1)), m.group(2)
            if last_head < n <= last_head + 3 and len(rest) <= 60 and "State" not in rest and "LGA" not in rest \
                    and not rest[0].isdigit() and not rest.startswith(FIELD_START):
                if cur:
                    entries.append(cur)
                cur, last_head = [ln], n
                continue
        if cur:
            cur.append(ln)
    if cur:
        entries.append(cur)
    keep = [e for e in entries if state.lower() in " ".join(e).lower()]
    with open(out, "w") as f:
        f.write("\n=====\n".join("\n".join(l for l in e if l) for e in keep))
    print(f"{len(entries)} head entries (last number {last_head}); {len(keep)} name {state}")


if __name__ == "__main__":
    main()
