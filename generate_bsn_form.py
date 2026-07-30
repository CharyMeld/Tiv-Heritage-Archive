from fpdf import FPDF
import warnings
warnings.filterwarnings("ignore")

pdf = FPDF("P", "mm", "A4")
pdf.set_auto_page_break(False)
pdf.set_margins(18, 15, 18)

LM = 18
W  = 174
RX = LM + W   # 192

GAP = 5  # mm gap between label and answer

# ── helpers ──────────────────────────────────────────────────────────────
def T(x, y, txt, bold=False, size=9, italic=False, w=None):
    s = ("B" if bold else "") + ("I" if italic else "")
    pdf.set_font("Helvetica", s, size)
    pdf.set_xy(x, y)
    pdf.cell(w or (RX - x), 5, str(txt), 0, 0)

def MT(x, y, wd, h, txt, bold=False, size=9):
    pdf.set_font("Helvetica", "B" if bold else "", size)
    pdf.set_xy(x, y)
    pdf.multi_cell(wd, h, txt, 0, "L")
    return pdf.get_y()

def UL(x1, y, x2):
    pdf.set_draw_color(0, 0, 0)
    pdf.set_line_width(0.25)
    pdf.line(x1, y, x2, y)

def CB(x, y, checked=False, s=3.5):
    pdf.set_draw_color(0, 0, 0)
    pdf.set_line_width(0.3)
    pdf.rect(x, y, s, s)
    if checked:
        pdf.set_font("Helvetica", "B", 8)
        pdf.set_xy(x + 0.4, y - 0.2)
        pdf.cell(s, s + 0.5, "X", 0, 0)

def BOX(x, y, bw, bh):
    pdf.set_draw_color(0, 0, 0)
    pdf.set_line_width(0.5)
    pdf.rect(x, y, bw, bh)

def GBAR(y, label):
    pdf.set_fill_color(175, 175, 175)
    pdf.set_font("Helvetica", "B", 9)
    pdf.set_xy(LM, y)
    pdf.cell(W, 6, "  " + label, 0, 0, "L", fill=True)
    pdf.set_fill_color(255, 255, 255)

def HLINE(y):
    pdf.set_draw_color(0, 0, 0)
    pdf.set_line_width(0.3)
    pdf.line(LM, y, RX, y)

def FIELD(x, y, label, value, right=None):
    """Render LABEL  [value underlined]. GAP auto-measured from label width."""
    right = right or (RX - 3)
    pdf.set_font("Helvetica", "B", 9)
    lw = pdf.get_string_width(label)
    pdf.set_xy(x, y)
    pdf.cell(lw, 5, label, 0, 0)
    vx = x + lw + GAP
    pdf.set_font("Helvetica", "", 9)
    pdf.set_xy(vx, y)
    pdf.cell(right - vx, 5, value, 0, 0)
    UL(vx, y + 5, right)

# ═══════════════════════════════════════════════════════════════════
# PAGE 1
# ═══════════════════════════════════════════════════════════════════
pdf.add_page()
cy = 15

# Title
pdf.set_font("Helvetica", "B", 13)
pdf.set_xy(LM, cy)
pdf.cell(W, 8, "PERMISSION REQUEST FORM", 0, 0, "C")
cy += 11

# ── Address box ───────────────────────────────────────────────────
addr_y = cy
cy += 3
T(LM+3, cy, "Please, complete this form and send it by e-mail, or hand Delivery to:", bold=True, size=9)
cy += 5
for line in ["Olusegun Obadare", "Manager, Publishing", "The Bible Society of Nigeria",
             "18, Wharf, Road,", "P. O. Box 68", "Apapa, Lagos.",
             "Email: obadare@biblesociety-nigeria.org"]:
    T(LM+3, cy, line, bold=True, size=9)
    cy += 4.3
cy += 2
BOX(LM, addr_y, W, cy - addr_y)
cy += 5

# ── Translation requested ─────────────────────────────────────────
CB(LM, cy, checked=True)
pdf.set_font("Helvetica", "B", 10)
lw = pdf.get_string_width("Translation requested:")
T(LM+6, cy-0.5, "Translation requested:", bold=True, size=10, w=lw+1)
vx = LM + 6 + lw + GAP
T(vx, cy-0.5, "First Tiv Bible Translation", size=10, w=RX-vx)
UL(vx, cy+4.5, RX)
cy += 8

# ── Work requested ────────────────────────────────────────────────
CB(LM, cy, checked=True)
pdf.set_font("Helvetica", "B", 10)
lw2 = pdf.get_string_width("Work requested:")
T(LM+6, cy-0.5, "Work requested:", bold=True, size=10, w=lw2+1)
vx2 = LM + 6 + lw2 + GAP
T(vx2, cy-0.5, "Electronic digital soft copy for cultural heritage website", size=10, w=RX-vx2)
UL(vx2, cy+4.5, RX)
cy += 8

# ── New Request / Contract Renewal ────────────────────────────────
HLINE(cy); cy += 3
CB(LM, cy, checked=True)
T(LM+5, cy-0.3, "New Request", bold=True, size=9, w=22)
CB(LM+28, cy, checked=False)
T(LM+33, cy-0.3,
  "Contract Renewal (Please complete name, company name and any changed information)",
  size=8.5, w=W-33)
cy += 7

# ── Instruction ───────────────────────────────────────────────────
cy = MT(LM, cy, W, 4.5,
        "If your request is approved the contract will be done for the publication included in "
        "this request. Any other publication done with the same content but with a different "
        "presentation requires a separate contract.", size=8.5) + 4

# ── YOUR PRODUCT INFORMATION ──────────────────────────────────────
pi_y = cy
GBAR(cy, "YOUR PRODUCT INFORMATION"); cy += 8

# 1. Title
FIELD(LM+5, cy, "1.  TITLE OF PRODUCT:", "Tiv Heritage Archive"); cy += 8

# 2. Format
pdf.set_font("Helvetica","B",9)
lw_fmt = pdf.get_string_width("2.  FORMAT:")
T(LM+5, cy, "2.  FORMAT:", bold=True, size=9, w=lw_fmt+1)
fmts = [("PRINT",False,14),("CDROM",False,15),("CD",False,9),
        ("AUDIO",False,14),("DVD",False,11),("VHS",False,11),("WEBSITE",True,20)]
fx = LM+5+lw_fmt+GAP
for lbl, chk, fw in fmts:
    CB(fx, cy, checked=chk)
    T(fx+4.5, cy-0.3, lbl, size=9, w=fw)
    fx += fw + 5
cy += 6
CB(LM+24, cy, checked=False)
T(LM+29, cy-0.3, "OTHER:", size=9, w=14); UL(LM+44, cy+4.5, LM+72)
cy += 7

# 3. Description
T(LM+5, cy, "3.  PRODUCT DESCRIPTION OR DETAIL DESCRIPTION OF THE PROJECT:",
  bold=True, size=9)
cy += 6
desc = ("Tiv Heritage Archive (https://www.tivheritage.com) is a comprehensive digital "
        "cultural preservation platform dedicated to documenting, safeguarding, and promoting the "
        "language, history, culture, and traditions of the Tiv people of North Central Nigeria. "
        "The platform provides free public access to Tiv vocabulary, proverbs, folklore, traditional "
        "food, festivals, music, names, and cultural knowledge. The Tiv Bible text will be integrated "
        "as a sacred linguistic and cultural reference resource within the archive's Scripture and "
        "Language section, allowing users to read, search, and reference the First Tiv Bible "
        "Translation in its original translated form. The text will be displayed with full attribution "
        "to the Bible Society of Nigeria and will not be modified or redistributed outside this platform.")
cy = MT(LM+5, cy, W-8, 4.3, desc, size=8.5) + 2

# Other translations
pdf.set_font("Helvetica","B",9)
lw_bt = pdf.get_string_width("Are there other Bible Translations in this product:")
T(LM+5, cy, "Are there other Bible Translations in this product:", bold=True, size=9, w=lw_bt+1)
ox = LM+5+lw_bt+GAP
CB(ox, cy, False);   T(ox+4.5, cy-0.3, "Yes", size=9, w=10)
ox2 = ox+15; CB(ox2, cy, True); T(ox2+4.5, cy-0.3, "No", size=9, w=8)
cy += 6
FIELD(LM+5, cy, "Which other translation (be specific):", "N/A"); cy += 7

# 4. Percentage
pdf.set_font("Helvetica","B",9)
lw_pct = pdf.get_string_width("4.  PERCENTAGE OF CONTENT:")
T(LM+5, cy, "4.  PERCENTAGE OF CONTENT:", bold=True, size=9, w=lw_pct+1)
px = LM+5+lw_pct+GAP
T(px, cy, "Bible Text", size=9, w=16)
T(px+17, cy, "15%", bold=True, size=9, w=10)
T(px+28, cy, "Additional Material", size=9, w=32)
T(px+61, cy, "85%", bold=True, size=9, w=10)
cy += 6

T(LM+5, cy, "DESCRIBE ADDITIONAL MATERIAL:", bold=True, size=9)
cy += 5
cy = MT(LM+5, cy, W-8, 4.5,
        "Tiv language vocabulary, cultural practices, traditions, proverbs, folklore, "
        "music, food, names, historical records, and community cultural content.", size=8.5) + 1
T(LM+5, cy,
  "BSN will verify this information with the final publication to confirm this request is fulfilled.",
  italic=True, size=8)
cy += 5
BOX(LM, pi_y, W, cy - pi_y)

# ═══════════════════════════════════════════════════════════════════
# PAGE 2
# ═══════════════════════════════════════════════════════════════════
pdf.add_page()
cy = 15
p2_y = cy

# 5. Location
T(LM+5, cy, "5.  LOCATION OF PRODUCTION DISTRIBUTION:", bold=True, size=9); cy += 6
for lbl, chk in [("NIGERIA",False),("WORLDWIDE",True),("ONLY TO THE FOLLOWING COUNTRIES:",False)]:
    CB(LM+25, cy, checked=chk)
    T(LM+31, cy-0.3, lbl, size=9, w=70)
    cy += 6
cy += 2

# 6. Purpose
pdf.set_font("Helvetica","B",9)
lw_pur = pdf.get_string_width("6.  PURPOSE:")
T(LM+5, cy, "6.  PURPOSE:", bold=True, size=9, w=lw_pur+1)
pcbx = LM+5+lw_pur+GAP
CB(pcbx, cy, False); T(pcbx+4.5, cy-0.3, "COMMERCIAL", size=9, w=30); cy += 6
T(LM+30, cy, "$", size=9, w=6); UL(LM+36, cy+4.5, LM+68)
T(LM+70, cy, "Approximate retail price", bold=True, size=9, w=50); cy += 6
CB(LM+25, cy, False); T(LM+31, cy-0.3, "COMMERCIAL", size=9, w=30); cy += 6
T(LM+30, cy, "$", size=9, w=6); UL(LM+36, cy+4.5, LM+68)
T(LM+70, cy, "It will be sold at production price", bold=True, size=9, w=60); cy += 7

CB(LM+25, cy, True)
T(LM+31, cy-0.3, "NON-COMMERCIAL", bold=True, size=9, w=34)
T(LM+68, cy-0.3,
  "(will be distributed free, we will not receive any compensation, any funds",
  size=8.5, w=RX-LM-68); cy += 5
T(LM+68, cy-0.3,
  "or any income, nor donations. All costs will be assumed by our company.)",
  size=8.5, w=RX-LM-68); cy += 6

CB(LM+25, cy, False)
T(LM+31, cy-0.3, "DONATIONS WILL BE RECEIVED", size=9, w=60); cy += 8

T(LM+5, cy, "If you answered COMMERCIAL, please respond 7, 8, 9, and 10.", bold=True, size=9); cy += 5
T(LM+5, cy, "If you answered NON-COMMERCIAL, please go directly to 11.", bold=True, size=9); cy += 8

# 7.
FIELD(LM+5, cy, "7.  APPROXIMATE PRICE OF THE PRODUCT", "N/A -- Non-Commercial"); cy += 7

# 8.
pdf.set_font("Helvetica","B",9)
lw_disc = pdf.get_string_width("8.  MAXIMUM DISCOUNT THAT WILL BE OFFERED:")
T(LM+5, cy, "8.  MAXIMUM DISCOUNT THAT WILL BE OFFERED:", bold=True, size=9, w=lw_disc+1)
dx = LM+5+lw_disc+GAP
T(dx, cy, "N/A", size=9, w=12); UL(dx, cy+5, dx+11)
T(dx+13, cy, "% in Nigeria", size=9, w=24); UL(dx+13, cy+5, dx+35)
T(dx+37, cy, "% Internationally", size=9, w=30); cy += 7

# 9. Sales
T(LM+5, cy, "9.  SALES PROJECTION IN U.S. DOLLARS:", bold=True, size=9); cy += 6
for lbl in ["FIRST YEAR:    $  N/A","SECOND YEAR: $  N/A","THIRD YEAR:   $  N/A","TOTAL:              $  N/A"]:
    T(LM+22, cy, lbl, size=9, w=55); cy += 4.5
cy += 2
T(LM+22, cy, "DISTRIBUTION PROJECTION IN UNITS:", bold=True, size=9); cy += 5
for lbl in ["FIRST YEAR          N/A","SECOND YEAR      N/A","THIRD YEAR          N/A","TOTAL                   N/A"]:
    T(LM+22, cy, lbl, size=9, w=55); cy += 4.5
cy += 3

# 10.
T(LM+5, cy,
  "10. Indicate the amount you propose to spend on marketing to launch this product:  US$  N/A",
  bold=True, size=9); cy += 8

# 11a. Marketing strategy
T(LM+5, cy, "11. What will be your marketing strategy?", bold=True, size=9, w=72)
cy = MT(LM+78, cy, W-78, 4.5,
        "Outreach through Tiv diaspora social media communities; partnerships with Nigerian "
        "universities and linguistics departments; collaboration with Tiv cultural associations "
        "and community leaders; promotion through cultural preservation networks.", size=8.5) + 2

# 11b. Release date
FIELD(LM+5, cy, "11. PROJECTED RELEASE DATE:", "August 2026"); cy += 7

# 12. Channels
T(LM+5, cy, "12. CHANNELS OF DISTRIBUTION:", bold=True, size=9); cy += 6
channels = [("CHURCHES",False),("BOOKSTORES & SPECIALTY",False),("DISCOUNT STORES",False),
            ("WHOLESALERS",False),("END USERS",True),("MISSIONS/AGENCIES",False),
            ("BOOK CLUBS",False),("SHOPPING CLUBS",False)]
row = []
for lbl, chk in channels:
    row.append((lbl, chk))
    if len(row) == 3:
        fx = LM+10
        for l, c in row:
            CB(fx, cy, c)
            pdf.set_font("Helvetica","",8.5)
            lw = pdf.get_string_width(l)
            pdf.set_xy(fx+4.5, cy-0.3)
            pdf.cell(lw+2, 5, l, 0, 0)
            fx += lw + 12
        cy += 6; row = []
if row:
    fx = LM+10
    for l, c in row:
        CB(fx, cy, c)
        pdf.set_font("Helvetica","",8.5)
        lw = pdf.get_string_width(l)
        pdf.set_xy(fx+4.5, cy-0.3)
        pdf.cell(lw+2, 5, l, 0, 0)
        fx += lw + 12
    cy += 6
cy += 2
BOX(LM, p2_y, W, cy - p2_y)

# ═══════════════════════════════════════════════════════════════════
# PAGE 3
# ═══════════════════════════════════════════════════════════════════
pdf.add_page()
cy = 15

BOX(LM, cy, W, 18); cy += 22

# ── SOLICITOR INFORMATION ─────────────────────────────────────────
si_y = cy
GBAR(cy, "SOLICITOR INFORMATION"); cy += 8

# Company Name & Contact Person (two-column row)
half = W / 2
pdf.set_font("Helvetica","B",9)
lw = pdf.get_string_width("COMPANY NAME:")
T(LM+3, cy, "COMPANY NAME:", bold=True, size=9, w=lw+1)
vx = LM+3+lw+GAP
T(vx, cy, "TeamO Digital Solutions", size=9, w=LM+half-vx-2)
UL(vx, cy+5, LM+half-2)

lw2 = pdf.get_string_width("CONTACT PERSON:")
T(LM+half+3, cy, "CONTACT PERSON:", bold=True, size=9, w=lw2+1)
vx2 = LM+half+3+lw2+GAP
T(vx2, cy, "Charles Ikyese", size=9, w=RX-3-vx2)
UL(vx2, cy+5, RX-3)
cy += 8

FIELD(LM+3, cy, "TITLE:", "Project Lead Developer"); cy += 8

FIELD(LM+3, cy, "ADDRESS:",
      "Plot 40 Rasco Close, North Gate Bus Stop, Sasa, Ibadan, Oyo State, Nigeria"); cy += 8

# City / State / ZIP
pdf.set_font("Helvetica","B",9)
lw_c = pdf.get_string_width("CITY:")
T(LM+3, cy, "CITY:", bold=True, size=9, w=lw_c+1)
vx_c = LM+3+lw_c+GAP
T(vx_c, cy, "Ibadan", size=9, w=55); UL(vx_c, cy+5, vx_c+42)

lw_s = pdf.get_string_width("STATE:")
sx = vx_c+48
T(sx, cy, "STATE:", bold=True, size=9, w=lw_s+1)
vx_s = sx+lw_s+GAP
T(vx_s, cy, "Oyo State", size=9, w=36); UL(vx_s, cy+5, vx_s+28)

lw_z = pdf.get_string_width("ZIP:")
zx = vx_s+32
T(zx, cy, "ZIP:", bold=True, size=9, w=lw_z+1)
vx_z = zx+lw_z+GAP
T(vx_z, cy, "200132", size=9, w=RX-3-vx_z); UL(vx_z, cy+5, RX-3)
cy += 8

# Business Phone & Fax
pdf.set_font("Helvetica","B",9)
lw_bp = pdf.get_string_width("BUSINESS PHONE:")
T(LM+3, cy, "BUSINESS PHONE:", bold=True, size=9, w=lw_bp+1)
vx_bp = LM+3+lw_bp+GAP
T(vx_bp, cy, "+2349032808107", size=9, w=48); UL(vx_bp, cy+5, vx_bp+40)

lw_bf = pdf.get_string_width("BUSINESS FAX:")
fx_b = vx_bp+45
T(fx_b, cy, "BUSINESS FAX:", bold=True, size=9, w=lw_bf+1)
vx_bf = fx_b+lw_bf+GAP
T(vx_bf, cy, "N/A", size=9, w=RX-3-vx_bf); UL(vx_bf, cy+5, RX-3)
cy += 8

FIELD(LM+3, cy, "E-MAIL ADDRESS:", "charlesikyese@gmail.com"); cy += 8

FIELD(LM+3, cy, "WEBSITE:", "https://www.tivheritage.com"); cy += 8

# Incorporated?
pdf.set_font("Helvetica","B",9)
lw_inc = pdf.get_string_width("IS THE COMPANY INCORPORATED?")
T(LM+3, cy, "IS THE COMPANY INCORPORATED?", bold=True, size=9, w=lw_inc+1)
ox = LM+3+lw_inc+GAP
CB(ox, cy, False);  T(ox+4.5, cy-0.3, "YES", size=9, w=10)
ox2 = ox+16; CB(ox2, cy, True); T(ox2+4.5, cy-0.3, "NO", size=9, w=8)
ox3 = ox2+16; CB(ox3, cy, False); T(ox3+4.5, cy-0.3, "OTHER:", size=9, w=14)
UL(ox3+20, cy+5, RX-3); cy += 8

FIELD(LM+3, cy, "CORPORATION NAME:", "TeamO Digital Solutions"); cy += 7

CB(LM+3, cy, True);  T(LM+8, cy-0.3, "NON PROFIT", size=9, w=22)
CB(LM+32, cy, False); T(LM+37, cy-0.3, "FOR PROFIT", size=9, w=22)
cy += 7

FIELD(LM+3, cy, "YEARS IN BUSINESS:", "3 years (Founded 2023)"); cy += 8

# Tell us more
cy = MT(LM+3, cy, W-6, 4.5,
        "Tell us more about your company, who you are, what you do and what qualifies you to produce "
        "the kind of product you are requesting to use our Bible Text.", size=8.5) + 3

bio = ("TeamO Digital Solutions (TDS) is a technology company founded in 2023, specializing in "
       "records digitalization, IT support, and consultancy. The Tiv Heritage Archive project was "
       "initiated by TDS as part of its records digitalization mission, aimed at preserving and "
       "promoting the language, history, and cultural heritage of the Tiv people of North Central "
       "Nigeria. Charles Ikyese serves as the TDS Software Developer in charge of building and "
       "managing the Tiv Heritage Archive platform. With proven expertise in developing database-driven "
       "web platforms (PHP, MySQL, HTML5), TDS is fully qualified to responsibly develop, host, and "
       "maintain this digital cultural archive. The First Tiv Bible Translation will be treated with "
       "the utmost reverence -- presented faithfully, fully attributed to the Bible Society of Nigeria, "
       "and never altered or redistributed outside the approved platform.")
cy = MT(LM+3, cy, W-6, 4.5, bio, size=8.5) + 3

BOX(LM, si_y, W, cy - si_y)

# ═══════════════════════════════════════════════════════════════════
# PAGE 4
# ═══════════════════════════════════════════════════════════════════
pdf.add_page()
cy = 15

cr_y = cy
GBAR(cy, "CREDIT REFERENCES"); cy += 8

def credit_block(num, cy, name, company, address, phone, fax, email):
    pdf.set_font("Helvetica","B",9)
    lw_n = pdf.get_string_width(f"{num}. Name:")
    T(LM+3, cy, f"{num}. Name:", bold=True, size=9, w=lw_n+1)
    T(LM+3+lw_n+GAP, cy, name, size=9, w=82-lw_n); UL(LM+3+lw_n+GAP, cy+5, LM+87)
    lw_nc = pdf.get_string_width("Name of company:")
    T(LM+89, cy, "Name of company:", bold=True, size=9, w=lw_nc+1)
    T(LM+89+lw_nc+GAP, cy, company, size=9, w=RX-3-(LM+89+lw_nc+GAP))
    UL(LM+89+lw_nc+GAP, cy+5, RX-3)
    cy += 7
    pdf.set_font("Helvetica","B",9)
    lw_a = pdf.get_string_width("Address:")
    T(LM+3, cy, "Address:", bold=True, size=9, w=lw_a+1)
    T(LM+3+lw_a+GAP, cy, address, size=8.5, w=RX-3-(LM+3+lw_a+GAP))
    UL(LM+3+lw_a+GAP, cy+5, RX-3)
    cy += 7
    pdf.set_font("Helvetica","B",9)
    lw_t = pdf.get_string_width("Telephone:")
    T(LM+3, cy, "Telephone:", bold=True, size=9, w=lw_t+1)
    T(LM+3+lw_t+GAP, cy, phone, size=9, w=42); UL(LM+3+lw_t+GAP, cy+5, LM+3+lw_t+GAP+38)
    lw_f = pdf.get_string_width("Fax:")
    fx_x = LM+3+lw_t+GAP+42
    T(fx_x, cy, "Fax:", bold=True, size=9, w=lw_f+1)
    T(fx_x+lw_f+GAP, cy, fax, size=9, w=20); UL(fx_x+lw_f+GAP, cy+5, fx_x+lw_f+GAP+18)
    lw_e = pdf.get_string_width("E-mail:")
    ex_x = fx_x+lw_f+GAP+22
    T(ex_x, cy, "E-mail:", bold=True, size=9, w=lw_e+1)
    T(ex_x+lw_e+GAP, cy, email, size=9, w=RX-3-(ex_x+lw_e+GAP))
    UL(ex_x+lw_e+GAP, cy+5, RX-3)
    cy += 9
    return cy

cy = credit_block("1", cy,
    name    = "Dr. Mrs. M Ojebode (CEO)",
    company = "TeamO Digital Solutions",
    address = "Plot 40 Rasco Close, North Gate Bus Stop, Sasa, Ibadan, Oyo State, Nigeria",
    phone   = "+234 805 732 6117",
    fax     = "",
    email   = "mojebode@gmail.com")

cy = credit_block("2", cy,
    name    = "DSC Mbakeren Ikeseh",
    company = "Nigeria Security and Civil Defence Corps",
    address = "Nigeria Security and Civil Defence Corps, Oyo State Command, Agodi, Ibadan, Oyo State",
    phone   = "+234 813 399 6544",
    fax     = "",
    email   = "mbakerenikeseh@gmail.com")

cy = credit_block("3", cy,
    name    = "Terungwa Emmanuel",
    company = "University of Lagos",
    address = "Department of Business Administration, University of Lagos",
    phone   = "09022945061",
    fax     = "",
    email   = "emmanuelterungwa2022@gmail.com")

cy += 2
BOX(LM, cr_y, W, cy - cr_y)
cy += 7

# ── Electronic files box ──────────────────────────────────────────
ef_y = cy; cy += 5

T(LM+5, cy, "Bible text requested in electronic files:", size=9, w=65)
fx = LM+71
for lbl, chk in [("CD",False),("Zip",False),("FTP",True)]:
    CB(fx, cy, chk); T(fx+4.5, cy-0.3, lbl, size=9, w=12); fx += 20
cy += 8

T(LM+5, cy, "Electronic files format :", size=9, w=42)
fx = LM+48
for lbl, chk in [("RTF",False),("XML",True),("USFM",False)]:
    CB(fx, cy, chk); T(fx+4.5, cy-0.3, lbl, size=9, w=14); fx += 20
UL(fx, cy+4.5, fx+22)
cy += 8

# ── Signature row ─ image bottom anchored to the underline ───────
SIG_IMG = "/opt/lampp/htdocs/Tiv-Heritage-Archive/signature_transparent.png"
SIG_W   = 38                      # mm wide
SIG_H   = SIG_W * (293 / 347)    # ~32 mm tall (natural aspect ratio)

# Reserve blank space above the label row so the image can float above it
cy += SIG_H - 6    # 6 mm of image will overlap the label/underline area
sig_y = cy         # "Signature" text and underline live here

# Place image so its bottom sits right on the underline (sig_y + 5)
pdf.image(SIG_IMG, x=LM+22, y=sig_y + 5 - SIG_H + 8, w=SIG_W)

T(LM+5,  sig_y, "Signature", size=9, w=18)
UL(LM+24, sig_y+5, LM+86)
T(LM+93, sig_y, "Date:", size=9, w=12)
T(LM+105, sig_y, "June 13, 2026", size=9, w=50)
UL(LM+105, sig_y+5, RX-3)
cy = sig_y + 9

BOX(LM, ef_y, W, cy - ef_y)

out = "/opt/lampp/htdocs/Tiv-Heritage-Archive/BSN_Permission_Request_FILLED.pdf"
pdf.output(out)
print(f"Saved: {out}")
