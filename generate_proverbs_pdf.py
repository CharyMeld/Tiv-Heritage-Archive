#!/usr/bin/env python3
r"""
Generates the "Tiv Proverbs" PDF — a link-bait/shareable resource for the
Tiv Heritage Archive outreach plan (see OUTREACH_PLAN.md, Tier 3).

Input: a JSON export of tiv_proverbs (id, tiv_text, english_translation,
deeper_meaning, category). Regenerate the export via SSH against the live DB:

    ssh root@72.62.119.237 "cd /var/www/tiv && php -r \"
    define('BASE_PATH', '/var/www/tiv');
    require BASE_PATH.'/config/config.php';
    require BASE_PATH.'/config/database.php';
    \\\$db = Database::getInstance();
    \\\$rows = \\\$db->query('SELECT id, tiv_text, english_translation, deeper_meaning, category FROM tiv_proverbs ORDER BY tiv_text')->fetchAll();
    echo json_encode(\\\$rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    \"" > proverbs_export.json

Usage: python3 generate_proverbs_pdf.py
Output: uploads/downloads/tiv-proverbs.pdf
"""
import json
import os
from fpdf import FPDF

HERE = os.path.dirname(os.path.abspath(__file__))
DATA_FILE = os.path.join(HERE, "proverbs_export.json")
OUT_DIR = os.path.join(HERE, "uploads", "downloads")
OUT_FILE = os.path.join(OUT_DIR, "tiv-proverbs.pdf")

FONT_DIR = "/usr/share/fonts/truetype/dejavu"
FONT_REGULAR = os.path.join(FONT_DIR, "DejaVuSans.ttf")
FONT_BOLD = os.path.join(FONT_DIR, "DejaVuSans-Bold.ttf")

# Heritage brand colors (assets/css/styles.css --color-primary etc.)
BROWN = (92, 58, 33)      # #5C3A21
BROWN_LIGHT = (123, 79, 46)  # #7B4F2E
MUTED = (122, 106, 90)    # #7a6a5a
CREAM = (250, 247, 242)   # #FAF7F2

SITE_URL = "https://www.tivheritage.com"

with open(DATA_FILE, encoding="utf-8") as f:
    proverbs = json.load(f)

proverbs.sort(key=lambda p: (p["tiv_text"] or "").lower())


class ProverbsPDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return
        self.set_font("DejaVu", "", 8)
        self.set_text_color(*MUTED)
        self.set_y(10)
        self.cell(0, 5, "Tiv Heritage Archive — Tiv Proverbs Collection", align="L")
        self.set_y(10)
        self.cell(0, 5, SITE_URL, align="R")
        self.set_draw_color(*BROWN_LIGHT)
        self.set_line_width(0.3)
        self.line(18, 16, 192, 16)
        self.set_y(20)

    def footer(self):
        if self.page_no() == 1:
            return
        self.set_y(-15)
        self.set_font("DejaVu", "", 8)
        self.set_text_color(*MUTED)
        self.cell(0, 10, f"Page {self.page_no() - 1}  ·  tivheritage.com", align="C")


pdf = ProverbsPDF("P", "mm", "A4")
pdf.add_font("DejaVu", "", FONT_REGULAR)
pdf.add_font("DejaVu", "B", FONT_BOLD)
pdf.set_auto_page_break(True, margin=20)
pdf.set_margins(18, 15, 18)

# ── Cover page ────────────────────────────────────────────────────────────
pdf.add_page()
pdf.set_fill_color(*CREAM)
pdf.rect(0, 0, 210, 297, "F")

pdf.set_y(90)
pdf.set_font("DejaVu", "B", 34)
pdf.set_text_color(*BROWN)
pdf.cell(0, 16, "Tiv Proverbs", align="C")
pdf.ln(18)

pdf.set_font("DejaVu", "", 15)
pdf.set_text_color(*BROWN_LIGHT)
pdf.cell(0, 10, "A Collection from the Tiv Heritage Archive", align="C")
pdf.ln(14)

pdf.set_font("DejaVu", "", 11)
pdf.set_text_color(*MUTED)
pdf.multi_cell(
    0, 6,
    f"{len(proverbs)} traditional Tiv proverbs with English translations\n"
    "and their deeper cultural meanings.",
    align="C",
)
pdf.ln(20)

pdf.set_font("DejaVu", "B", 12)
pdf.set_text_color(*BROWN)
pdf.cell(0, 8, SITE_URL, align="C")
pdf.ln(6)
pdf.set_font("DejaVu", "", 9)
pdf.set_text_color(*MUTED)
pdf.cell(0, 6, "Free to share and print for education, research, and cultural preservation.", align="C")

# ── Proverb entries ───────────────────────────────────────────────────────
pdf.add_page()
pdf.set_auto_page_break(True, margin=20)

for i, p in enumerate(proverbs, start=1):
    # Keep a Tiv/translation pair together where reasonably possible
    if pdf.get_y() > 250:
        pdf.add_page()

    pdf.set_font("DejaVu", "B", 11)
    pdf.set_text_color(*BROWN)
    num_w = 10
    y_start = pdf.get_y()
    pdf.set_xy(18, y_start)
    pdf.cell(num_w, 6, f"{i}.")
    pdf.set_xy(18 + num_w, y_start)
    pdf.multi_cell(174 - num_w, 6, p["tiv_text"] or "")

    pdf.set_x(18 + num_w)
    pdf.set_font("DejaVu", "", 10)
    pdf.set_text_color(40, 40, 40)
    pdf.multi_cell(174 - num_w, 5.5, f"“{p['english_translation']}”" if p.get("english_translation") else "")

    if p.get("deeper_meaning"):
        pdf.set_x(18 + num_w)
        pdf.set_font("DejaVu", "", 9)
        pdf.set_text_color(*MUTED)
        pdf.multi_cell(174 - num_w, 5, p["deeper_meaning"])

    pdf.ln(4)

# ── Closing CTA page ─────────────────────────────────────────────────────
pdf.add_page()
pdf.set_y(120)
pdf.set_font("DejaVu", "B", 16)
pdf.set_text_color(*BROWN)
pdf.cell(0, 10, "Explore More Tiv Culture & Language", align="C")
pdf.ln(12)
pdf.set_font("DejaVu", "", 11)
pdf.set_text_color(40, 40, 40)
pdf.multi_cell(
    0, 7,
    "The Tiv Heritage Archive is a free, ever-growing digital archive of Tiv\n"
    "dictionary entries, names, plants, festivals, foods, animals, historical\n"
    "figures, and more.",
    align="C",
)
pdf.ln(8)
pdf.set_font("DejaVu", "B", 13)
pdf.set_text_color(*BROWN)
pdf.cell(0, 8, SITE_URL, align="C")

os.makedirs(OUT_DIR, exist_ok=True)
pdf.output(OUT_FILE)
print(f"Wrote {OUT_FILE} ({len(proverbs)} proverbs, {pdf.page_no()} pages)")
