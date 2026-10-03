"""
Parse an INEC "Directory of Polling Units" (revised January 2015) PDF into data/inec_<state>_ras.json,
the input of inec_wards_batch.py.

Usage: python3 inec_parse_directory.py <directory.pdf> <state slug> [<existing json to compare>]

Reads (pdftotext -layout):
  * the state summary table "THE LIST OF LOCAL GOVERNMENT AREAS" (name, code, # of RAs, # of PUs);
  * each LGA's table "THE LIST OF REGISTRATION AREAS IN THE LOCAL GOVERNMENT AREA" (NAME, code, # of PUs);
  * the mixed-case headers "RA: <Name>  Code: nn" under each "LGA: <NAME>  Code: nn", for the ward
    names as INEC writes them in normal case.
Refuses to write unless, for every LGA, the number of wards and the sum of their polling units match
the summary table, and every ward in the capitals table has a mixed-case header.
"""
import json, os, re, subprocess, sys

HERE = os.path.dirname(os.path.abspath(__file__))


def slug(n):
    return re.sub(r"[^a-z0-9]+", "-", n.lower().replace("‐", "-")).strip("-")


def main():
    pdf, state = sys.argv[1], sys.argv[2]
    txt = subprocess.run(["pdftotext", "-layout", pdf, "-"], capture_output=True, text=True).stdout
    lines = txt.split("\n")
    summary, i = {}, 0
    while i < len(lines) and "THE LIST OF LOCAL GOVERNMENT AREAS" not in lines[i]:
        i += 1
    for ln in lines[i + 1:]:
        if "TOTAL:" in ln:
            break
        m = re.match(r"^\s*(\S.*?)\s{2,}(\d{2})\s+(\d+)\s+([\d,]+)\s*$", ln)
        if m:
            summary[m.group(2)] = dict(name=m.group(1).strip(), nra=int(m.group(3)), npu=int(m.group(4).replace(",", "")))
    lgas, cur, mixed, in_table, pending = {}, None, {}, False, None
    for ln in lines:
        m = re.match(r"^LGA:\s*(\S.*?)\s*$", ln)
        if m and "Code" not in ln:  # 'LGA: NAME' then 'Code: 01' on the next line (table page)
            pending = m.group(1); continue
        m = re.match(r"^Code:\s*(\d{2})\s*$", ln)
        if m and pending:
            cur = m.group(1); lgas.setdefault(cur, dict(ras=[])); pending = None; continue
        m = re.match(r"^LGA:\s*(\S.*?)\s{2,}Code:\s*(\d{2})", ln)
        if m:
            cur = m.group(2); continue
        if "THE LIST OF REGISTRATION AREAS" in ln:
            in_table = True; continue
        if in_table:
            if "TOTAL:" in ln:
                in_table = False; continue
            m = re.match(r"^\s*(\S.*?)\s{2,}(\d{2})\s+(\d+)\s*$", ln)
            # A name that fills its column is followed by one space only (Lagos: Apapa 06, Kosofe 05).
            m = m or re.match(r"^\s*(\S.*?)\s(\d{2})\s{2,}(\d+)\s*$", ln)
            if m and cur and not ln.strip().startswith("NAME"):
                lgas[cur]["ras"].append(dict(upper=m.group(1).strip(), code=m.group(2), pus=int(m.group(3))))
            continue
        m = re.match(r"^RA:\s*(\S.*?)\s{2,}Code:\s*(\d{2})", ln)
        if m and cur:
            mixed[(cur, m.group(2))] = m.group(1).strip()
    errors, out = [], {}
    for code, s in sorted(summary.items()):
        ras = lgas.get(code, dict(ras=[]))["ras"]
        # the capitals table may repeat on continuation pages: keep the first occurrence of each code
        seen, uniq = set(), []
        for r in ras:
            if r["code"] not in seen:
                seen.add(r["code"]); uniq.append(r)
        for r in uniq:
            r["name"] = mixed.get((code, r["code"]))
            if not r["name"]:
                errors.append(f"{s['name']} {r['code']} {r['upper']}: no mixed-case header")
        if len(uniq) != s["nra"] or sum(r["pus"] for r in uniq) != s["npu"]:
            errors.append(f"{s['name']}: {len(uniq)} RAs / {sum(r['pus'] for r in uniq)} PUs, summary says {s['nra']} / {s['npu']}")
        out[code] = dict(name=s["name"].replace("‐", "-"), ras=uniq, nra=s["nra"], npu=s["npu"], slug=slug(s["name"]))
    print(f"{len(out)} LGAs, {sum(v['nra'] for v in out.values())} RAs, {sum(v['npu'] for v in out.values()):,} PUs")
    if errors:
        print("REFUSED:\n  " + "\n  ".join(errors)); sys.exit(1)
    if len(sys.argv) > 3:  # validation against an earlier parse
        old = json.load(open(sys.argv[3]))
        diffs = [(c, r["code"], r["name"], o["name"], r["pus"], o["pus"]) for c in out for r, o in zip(out[c]["ras"], old[c]["ras"])
                 if (r["name"], r["pus"], r["code"]) != (o["name"], o["pus"], o["code"])]
        print(f"compared with {sys.argv[3]}: {len(diffs)} differences", diffs[:10]); return
    path = os.path.join(HERE, "data", f"inec_{state}_ras.json")
    json.dump(out, open(path, "w"), indent=1, ensure_ascii=False)
    print("wrote", path)


if __name__ == "__main__":
    main()
