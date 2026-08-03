#!/usr/bin/env python3
r"""
Generates the "Tiv Alphabet" PDF — a link-bait/shareable resource for the
Tiv Heritage Archive outreach plan (see OUTREACH_PLAN.md, Tier 3).

Input: a JSON export of tiv_alphabet (type, letter, english_letter, ipa,
sound_desc, tiv_example, english_meaning, sort_order). Regenerate the
export via SSH against the live DB:

    ssh root@72.62.119.237 "cd /var/www/tiv && php -r \"
    define('BASE_PATH', '/var/www/tiv');
    require BASE_PATH.'/config/config.php';
    require BASE_PATH.'/config/database.php';
    \\\$db = Database::getInstance();
    \\\$rows = \\\$db->query('SELECT type, letter, english_letter, ipa, sound_desc, tiv_example, english_meaning, sort_order FROM tiv_alphabet ORDER BY FIELD(type,\\\\'plain\\\\',\\\\'vowel\\\\',\\\\'consonant\\\\',\\\\'digraph\\\\',\\\\'tonal\\\\'), sort_order ASC, id ASC')->fetchAll();
    echo json_encode(\\\$rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    \"" > alphabet_export.json

Usage: python3 generate_alphabet_pdf.py
Output: uploads/downloads/tiv-alphabet.pdf
"""
import json
import os
from fpdf import FPDF

HERE = os.path.dirname(os.path.abspath(__file__))
DATA_FILE = os.path.join(HERE, "alphabet_export.json")
OUT_DIR = os.path.join(HERE, "uploads", "downloads")
OUT_FILE = os.path.join(OUT_DIR, "tiv-alphabet.pdf")

FONT_DIR = "/usr/share/fonts/truetype/dejavu"
FONT_REGULAR = os.path.join(FONT_DIR, "DejaVuSans.ttf")
FONT_BOLD = os.path.join(FONT_DIR, "DejaVuSans-Bold.ttf")

# Heritage brand colors (assets/css/styles.css --color-primary etc.)
BROWN = (92, 58, 33)          # #5C3A21
BROWN_LIGHT = (123, 79, 46)   # #7B4F2E
MUTED = (122, 106, 90)        # #7a6a5a
CREAM = (250, 247, 242)       # #FAF7F2
VOWEL_BLUE = (45, 90, 158)    # #2d5a9e
DIGRAPH_GREEN = (74, 124, 89) # #4a7c59

SITE_URL = "https://www.tivheritage.com"

with open(DATA_FILE, encoding="utf-8") as f:
    rows = json.load(f)

grouped = {"plain": [], "vowel": [], "consonant": [], "digraph": [], "tonal": []}
for r in rows:
    grouped[r["type"]].append(r)

vowels = grouped["vowel"]
consonants = grouped["consonant"]
digraphs = grouped["digraph"]
tones = grouped["tonal"]


class AlphabetPDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return
        self.set_font("DejaVu", "", 8)
        self.set_text_color(*MUTED)
        self.set_y(10)
        self.cell(0, 5, "Tiv Heritage Archive — The Tiv Alphabet", align="L")
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


def section_heading(pdf, badge_letter, badge_color, title, subtitle):
    y = pdf.get_y()
    pdf.set_fill_color(*badge_color)
    pdf.ellipse(18, y, 7, 7, "F")
    pdf.set_xy(18, y)
    pdf.set_font("DejaVu", "B", 9)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(7, 7, badge_letter, align="C")
    pdf.set_xy(28, y - 0.5)
    pdf.set_font("DejaVu", "B", 15)
    pdf.set_text_color(*BROWN)
    pdf.cell(0, 8, title)
    pdf.ln(9)
    pdf.set_x(28)
    pdf.set_font("DejaVu", "", 10)
    pdf.set_text_color(*MUTED)
    pdf.cell(0, 6, subtitle)
    pdf.ln(10)


def letter_row(pdf, r, accent):
    if pdf.get_y() > 262:
        pdf.add_page()

    tiv_parts = (r["letter"] or "").split(" ", 1)
    cap = tiv_parts[0] if tiv_parts else r["letter"]
    small = tiv_parts[1] if len(tiv_parts) > 1 else ""

    y_start = pdf.get_y()
    pdf.set_xy(18, y_start)
    pdf.set_font("DejaVu", "B", 13)
    pdf.set_text_color(*accent)
    label = f"{cap} / {small}" if small else cap
    pdf.cell(30, 7, label)

    pdf.set_xy(50, y_start)
    pdf.set_font("DejaVu", "", 9)
    pdf.set_text_color(90, 90, 90)
    pdf.cell(22, 7, r.get("ipa") or "")

    pdf.set_xy(74, y_start)
    pdf.set_font("DejaVu", "", 9)
    pdf.set_text_color(40, 40, 40)
    example = r.get("tiv_example") or ""
    meaning = r.get("english_meaning") or ""
    ex_text = f"{example} — “{meaning}”" if example else ""
    pdf.multi_cell(60, 5, ex_text)

    pdf.set_xy(136, y_start)
    pdf.set_font("DejaVu", "", 8.5)
    pdf.set_text_color(*MUTED)
    pdf.multi_cell(56, 5, r.get("sound_desc") or "")

    row_h = max(pdf.get_y() - y_start, 7)
    pdf.set_y(y_start + row_h + 3)


pdf = AlphabetPDF("P", "mm", "A4")
pdf.add_font("DejaVu", "", FONT_REGULAR)
pdf.add_font("DejaVu", "B", FONT_BOLD)
pdf.set_auto_page_break(True, margin=20)
pdf.set_margins(18, 15, 18)

# ── Cover page ────────────────────────────────────────────────────────────
pdf.add_page()
pdf.set_fill_color(*CREAM)
pdf.rect(0, 0, 210, 297, "F")

pdf.set_y(85)
pdf.set_font("DejaVu", "B", 34)
pdf.set_text_color(*BROWN)
pdf.cell(0, 16, "The Tiv Alphabet", align="C")
pdf.ln(18)

pdf.set_font("DejaVu", "", 15)
pdf.set_text_color(*BROWN_LIGHT)
pdf.cell(0, 10, "Vowels, Consonants, Digraphs & Tone", align="C")
pdf.ln(14)

pdf.set_font("DejaVu", "", 11)
pdf.set_text_color(*MUTED)
pdf.multi_cell(
    0, 6,
    f"{len(vowels)} vowels, {len(consonants)} consonants, {len(digraphs)} digraphs,\n"
    "and a guide to the Tiv tonal system — from the Tiv Heritage Archive.",
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

# ── Writing system note ──────────────────────────────────────────────────
pdf.add_page()
section_heading(pdf, "✓", BROWN, "Writing System", "Direction, script & orthography")
pdf.set_x(18)
pdf.set_font("DejaVu", "", 10)
pdf.set_text_color(40, 40, 40)
pdf.multi_cell(
    174, 6,
    "Tiv is written left to right using the Latin script. There is no traditional "
    "indigenous script — the current writing system was developed in the 20th "
    "century with the aid of linguists and missionaries.\n\n"
    "The standard Tiv orthography used in schools and the Bible translation treats "
    "digraphs (gb, kp, mb, nd, ng, ny, ts) as single letters. Tone marks are used in "
    "formal linguistic texts but are often omitted in everyday writing.",
)
pdf.ln(8)

# ── Vowels ────────────────────────────────────────────────────────────────
section_heading(pdf, "V", VOWEL_BLUE, "Vowels", f"{len(vowels)} vowel sounds")
for r in vowels:
    letter_row(pdf, r, VOWEL_BLUE)
pdf.ln(4)
pdf.set_x(18)
pdf.set_font("DejaVu", "", 9)
pdf.set_text_color(*MUTED)
pdf.multi_cell(
    174, 5,
    "Note: vowels can be short or long. A long vowel is written by doubling the "
    "letter — a (short) vs aa (long). Long vowels carry a distinct meaning: "
    "or (person) vs oor (to be sick).",
)
pdf.ln(6)

# ── Consonants ────────────────────────────────────────────────────────────
pdf.add_page()
section_heading(pdf, "C", BROWN, "Consonants", f"{len(consonants)} consonant sounds")
for r in consonants:
    letter_row(pdf, r, BROWN)
pdf.ln(4)

# ── Digraphs ──────────────────────────────────────────────────────────────
pdf.add_page()
section_heading(pdf, "D", DIGRAPH_GREEN, "Digraphs", f"{len(digraphs)} two-letter sounds treated as single letters")
for r in digraphs:
    letter_row(pdf, r, DIGRAPH_GREEN)
pdf.ln(4)

# ── Tone ──────────────────────────────────────────────────────────────────
pdf.add_page()
section_heading(pdf, "T", BROWN_LIGHT, "Tone", "Tiv is a tonal language — pitch changes meaning")
for r in tones:
    letter_row(pdf, r, BROWN_LIGHT)

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
    "dictionary entries, proverbs, names, plants, festivals, foods, animals,\n"
    "historical figures, and more.",
    align="C",
)
pdf.ln(8)
pdf.set_font("DejaVu", "B", 13)
pdf.set_text_color(*BROWN)
pdf.cell(0, 8, SITE_URL, align="C")

os.makedirs(OUT_DIR, exist_ok=True)
pdf.output(OUT_FILE)
print(f"Wrote {OUT_FILE} ({len(vowels)} vowels, {len(consonants)} consonants, "
      f"{len(digraphs)} digraphs, {len(tones)} tone entries, {pdf.page_no()} pages)")
