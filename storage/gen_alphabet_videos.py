#!/usr/bin/env python3
"""
Tiv Alphabet Animated Video Generator
Produces one silent MP4 per entry (vowels, consonants, digraphs, tonals).
Audio is merged separately on the VPS using merge_audio.sh.

Usage:
    python3 gen_alphabet_videos.py           # generate all
    python3 gen_alphabet_videos.py test      # generate first entry only (preview)
"""

import os, sys, math, subprocess, tempfile
from PIL import Image, ImageDraw, ImageFont

# ── Output ────────────────────────────────────────────────────────────────
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, 'alphabet-videos')
os.makedirs(OUTPUT_DIR, exist_ok=True)

# ── Video settings ────────────────────────────────────────────────────────
W = H   = 1080
FPS     = 30
SECONDS = 5
FRAMES  = FPS * SECONDS   # 150

# ── Colours ───────────────────────────────────────────────────────────────
WHITE      = (255, 255, 255)
NEAR_BLACK = (15,  15,  15)
DARK       = (45,  45,  45)
MID        = (100, 100, 100)
LIGHT      = (170, 170, 170)
RULE       = (210, 210, 210)

# ── Fonts ─────────────────────────────────────────────────────────────────
F_SERIF_BOLD = '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf'
F_SERIF      = '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf'
F_SANS       = '/usr/share/fonts/truetype/ubuntu/Ubuntu-R.ttf'
F_SANS_BOLD  = '/usr/share/fonts/truetype/ubuntu/Ubuntu-B.ttf'

fonts = None   # lazy-loaded once per run

def load_fonts():
    global fonts
    if fonts:
        return
    fonts = {
        'letter':  ImageFont.truetype(F_SERIF_BOLD, 220),
        'ipa':     ImageFont.truetype(F_SERIF,       72),
        'desc':    ImageFont.truetype(F_SANS,        37),
        'example': ImageFont.truetype(F_SANS_BOLD,   58),
        'meaning': ImageFont.truetype(F_SANS,        40),
        'heading': ImageFont.truetype(F_SANS_BOLD,   21),
        'subhead': ImageFont.truetype(F_SANS,        18),
    }

# ── Easing ────────────────────────────────────────────────────────────────
def ease(t):
    return t * t * (3.0 - 2.0 * t)

def alpha_fade(frame, start, end, peak=255):
    if frame < start: return 0
    if frame >= end:  return peak
    return int(ease((frame - start) / (end - start)) * peak)

# ── Text helpers ──────────────────────────────────────────────────────────
def text_w(draw, text, font):
    return draw.textlength(text, font=font)

def wrap(draw, text, font, max_px):
    words = text.split()
    lines, line = [], ''
    for w in words:
        test = (line + ' ' + w).strip()
        if text_w(draw, test, font) > max_px and line:
            lines.append(line)
            line = w
        else:
            line = test
    if line:
        lines.append(line)
    return lines

def put(overlay_draw, text, cx, cy, font, rgb, alpha, anchor='mm'):
    r, g, b = rgb
    overlay_draw.text((cx, cy), text, font=font, fill=(r, g, b, alpha), anchor=anchor)

# ── Frame renderer ────────────────────────────────────────────────────────
CX = W // 2

def make_frame(f, entry):
    load_fonts()

    letter  = entry['letter']
    ipa     = entry['ipa']
    desc    = entry['sound_desc']
    example = entry['tiv_example']
    meaning = entry['english_meaning']
    etype   = entry['type'].upper()

    # Phase timings
    a_chrome  = alpha_fade(f,  0, 20)     # rules + header chrome
    a_letter  = alpha_fade(f,  0, 28)     # main letter
    a_ipa     = alpha_fade(f, 22, 52)     # IPA symbol
    a_divider = alpha_fade(f, 40, 60)     # mid divider line
    a_desc    = alpha_fade(f, 48, 80)     # sound description
    a_example = alpha_fade(f, 72, 105)    # example word + meaning

    # White RGB base
    base = Image.new('RGB', (W, H), WHITE)

    # Single RGBA overlay for all text + lines
    ov = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    d  = ImageDraw.Draw(ov)

    # ── Top chrome ──
    if a_chrome:
        put(d, 'TIV LANGUAGE ALPHABET', CX, 52, fonts['heading'], NEAR_BLACK, a_chrome)
        put(d, etype,                   CX, 78, fonts['subhead'], LIGHT,       a_chrome)
        d.line([(60, 104), (W - 60, 104)], fill=(*RULE, a_chrome), width=1)

    # ── Main letter ──
    if a_letter:
        put(d, letter, CX, 415, fonts['letter'], NEAR_BLACK, a_letter)

    # ── IPA ──
    if a_ipa:
        put(d, ipa, CX, 568, fonts['ipa'], DARK, a_ipa)

    # ── Mid divider ──
    if a_divider:
        rule_a = min(a_divider, 120)
        d.line([(160, 624), (W - 160, 624)], fill=(*RULE, rule_a), width=1)

    # ── Sound description ──
    if a_desc:
        tmp_d  = ImageDraw.Draw(Image.new('RGB', (W, H)))
        lines  = wrap(tmp_d, desc, fonts['desc'], 820)
        line_h = 48
        total_h = len(lines) * line_h
        y0 = 660 - (total_h - line_h) // 2   # centre block between divider + bottom
        for ln in lines:
            put(d, ln, CX, y0, fonts['desc'], MID, a_desc)
            y0 += line_h

    # ── Example section ──
    if a_example:
        put(d, 'EXAMPLE',        CX, 820, fonts['subhead'], LIGHT,      a_example)
        put(d, example,          CX, 860, fonts['example'], NEAR_BLACK,  a_example)
        put(d, f'"{meaning}"',   CX, 918, fonts['meaning'], MID,         a_example)

    # ── Bottom chrome ──
    if a_chrome:
        d.line([(60, 1008), (W - 60, 1008)], fill=(*RULE, a_chrome), width=1)

    # Composite
    base_rgba = base.convert('RGBA')
    result    = Image.alpha_composite(base_rgba, ov)
    return result.convert('RGB')

# ── Video builder ─────────────────────────────────────────────────────────
def generate(entry):
    safe = (entry['letter']
            .replace(' ', '_')
            .replace('/', '')
            .replace('(', '')
            .replace(')', '')
            .strip('_'))
    filename = f"{entry['type']}_{safe}.mp4"
    out_path = os.path.join(OUTPUT_DIR, filename)

    with tempfile.TemporaryDirectory() as tmp:
        for i in range(FRAMES):
            frame = make_frame(i, entry)
            frame.save(os.path.join(tmp, f'f{i:04d}.png'))

        r = subprocess.run([
            'ffmpeg', '-y',
            '-framerate', str(FPS),
            '-i', os.path.join(tmp, 'f%04d.png'),
            '-c:v', 'libx264',
            '-pix_fmt', 'yuv420p',
            '-crf', '20',
            '-preset', 'fast',
            '-movflags', '+faststart',
            out_path
        ], capture_output=True)

        if r.returncode != 0:
            print(f'    ✗  ffmpeg failed: {r.stderr[-300:].decode(errors="replace")}')
            return False

    kb = os.path.getsize(out_path) // 1024
    print(f'    ✓  {filename}  ({kb} KB)')
    return True

# ── Alphabet entries ──────────────────────────────────────────────────────
ENTRIES = [
    # ── Vowels ──
    {'type':'vowel',     'letter':'A a',      'ipa':'/a/',    'sound_desc':'said as "uh" — like u in sun, not a in cat',                                     'tiv_example':'bar',   'english_meaning':'salt'},
    {'type':'vowel',     'letter':'E e',      'ipa':'/e/',    'sound_desc':'like e in bed or net',                                                            'tiv_example':'se',    'english_meaning':'we / laugh'},
    {'type':'vowel',     'letter':'I i',      'ipa':'/i/',    'sound_desc':'like ee in feet or ea in beat',                                                   'tiv_example':'ime',   'english_meaning':'darkness'},
    {'type':'vowel',     'letter':'O o',      'ipa':'/o/',    'sound_desc':'like o in orbit and story',                                                       'tiv_example':'or',    'english_meaning':'person'},
    {'type':'vowel',     'letter':'U u',      'ipa':'/u/',    'sound_desc':'like oo in book or look',                                                         'tiv_example':'bagu',  'english_meaning':'monkey'},
    # ── Consonants ──
    {'type':'consonant', 'letter':'B b',      'ipa':'/b/',    'sound_desc':'like b in boy',                                                                   'tiv_example':'bam',   'english_meaning':'water'},
    {'type':'consonant', 'letter':'C c',      'ipa':'/tʃ/',   'sound_desc':'like ch in church — Tiv C always makes the "ch" sound',                           'tiv_example':'cia',   'english_meaning':'fear'},
    {'type':'consonant', 'letter':'D d',      'ipa':'/d/',    'sound_desc':'like d in dog',                                                                   'tiv_example':'ada',   'english_meaning':'bow'},
    {'type':'consonant', 'letter':'F f',      'ipa':'/f/',    'sound_desc':'like f in fish',                                                                  'tiv_example':'fa',    'english_meaning':'know'},
    {'type':'consonant', 'letter':'G g',      'ipa':'/ɡ/',    'sound_desc':'like g in go',                                                                    'tiv_example':'gar',   'english_meaning':'city / town'},
    {'type':'consonant', 'letter':'H h',      'ipa':'/h/',    'sound_desc':'like h in hat',                                                                   'tiv_example':'har',   'english_meaning':'hang'},
    {'type':'consonant', 'letter':'J j',      'ipa':'/dʒ/',   'sound_desc':'like g in gem — a soft j sound',                                                  'tiv_example':'ijen',  'english_meaning':'hunger'},
    {'type':'consonant', 'letter':'K k',      'ipa':'/k/',    'sound_desc':'like k in key',                                                                   'tiv_example':'koti',  'english_meaning':'court'},
    {'type':'consonant', 'letter':'L l',      'ipa':'/l/',    'sound_desc':'like l in love',                                                                  'tiv_example':'lam',   'english_meaning':'speak'},
    {'type':'consonant', 'letter':'M m',      'ipa':'/m/',    'sound_desc':'like m in man',                                                                   'tiv_example':'ma',    'english_meaning':'drink'},
    {'type':'consonant', 'letter':'N n',      'ipa':'/n/',    'sound_desc':'like n in now',                                                                   'tiv_example':'na',    'english_meaning':'give'},
    {'type':'consonant', 'letter':'P p',      'ipa':'/p/',    'sound_desc':'like p in pen',                                                                   'tiv_example':'per',   'english_meaning':'cross'},
    {'type':'consonant', 'letter':'R r',      'ipa':'/r/',    'sound_desc':'a rounded r — roll it slightly as in Irish English',                               'tiv_example':'ruam',  'english_meaning':'fufu / pounded yam'},
    {'type':'consonant', 'letter':'S s',      'ipa':'/s/',    'sound_desc':'like s in sun',                                                                   'tiv_example':'sar',   'english_meaning':'spread'},
    {'type':'consonant', 'letter':'T t',      'ipa':'/t/',    'sound_desc':'like t in top',                                                                   'tiv_example':'ter',   'english_meaning':'father'},
    {'type':'consonant', 'letter':'V v',      'ipa':'/v/',    'sound_desc':'like v in van',                                                                   'tiv_example':'ivo',   'english_meaning':'goat'},
    {'type':'consonant', 'letter':'W w',      'ipa':'/w/',    'sound_desc':'like w in water',                                                                 'tiv_example':'wase',  'english_meaning':'help'},
    {'type':'consonant', 'letter':'Y y',      'ipa':'/j/',    'sound_desc':'like y in yes',                                                                   'tiv_example':'ya',    'english_meaning':'eat'},
    {'type':'consonant', 'letter':'Z z',      'ipa':'/z/',    'sound_desc':'like z in zebra — used as a polite request form',                                  'tiv_example':'za',    'english_meaning':'go (request)'},
    # ── Digraphs ──
    {'type':'digraph',   'letter':'GB gb',    'ipa':'/ɡ͡b/',  'sound_desc':'labial-velar stop — g and b said together as one single sound',                   'tiv_example':'gba',   'english_meaning':'fall'},
    {'type':'digraph',   'letter':'KP kp',    'ipa':'/k͡p/',  'sound_desc':'labial-velar stop — k and p said together as one single sound',                   'tiv_example':'kper',  'english_meaning':'net / tomorrow'},
    {'type':'digraph',   'letter':'CH ch',    'ipa':'/tʃ/',   'sound_desc':'like ch in church — also written as C alone in some words',                       'tiv_example':'chia',  'english_meaning':'fear'},
    {'type':'digraph',   'letter':'SH sh',    'ipa':'/ʃ/',    'sound_desc':'like sh in shoe or ship',                                                         'tiv_example':'sha',   'english_meaning':'up / away'},
    {'type':'digraph',   'letter':'NY ny',    'ipa':'/ɲ/',    'sound_desc':'palatal nasal — like ny in canyon',                                               'tiv_example':'nyam',  'english_meaning':'meat'},
    {'type':'digraph',   'letter':'GH gh',    'ipa':'/ɣ/',    'sound_desc':'comes at the end of words — silent like gh in dough or borough',                  'tiv_example':'sugh',  'english_meaning':'thank'},
    {'type':'digraph',   'letter':'GW gw',    'ipa':'/ɡw/',   'sound_desc':'g and w blended together — like gw in Gwen',                                     'tiv_example':'gwa',   'english_meaning':'fame'},
    {'type':'digraph',   'letter':'KW kw',    'ipa':'/kw/',   'sound_desc':'like qu in queen or kw in awkward',                                               'tiv_example':'kwase', 'english_meaning':'woman'},
    {'type':'digraph',   'letter':'TS ts',    'ipa':'/ts/',   'sound_desc':'like ts in cats or fits',                                                         'tiv_example':'tsar',  'english_meaning':'bridge'},
    {'type':'digraph',   'letter':'MB mb',    'ipa':'/ᵐb/',   'sound_desc':'prenasalized b — hum through the nose before the b',                              'tiv_example':'mban',  'english_meaning':'night'},
    {'type':'digraph',   'letter':'ND nd',    'ipa':'/ⁿd/',   'sound_desc':'prenasalized d — nasal onset before the d',                                       'tiv_example':'nder',  'english_meaning':'wall'},
    {'type':'digraph',   'letter':'NG ng',    'ipa':'/ŋ/',    'sound_desc':'nasal — like ng in sing or ring',                                                 'tiv_example':'nger',  'english_meaning':'goat'},
    {'type':'digraph',   'letter':'BW bw',    'ipa':'/bw/',   'sound_desc':'b and w blended together — lips round into the b',                                'tiv_example':'bwagi', 'english_meaning':'pick / digging tool'},
    # ── Tonal marks ──
    {'type':'tonal',     'letter':'á  High',  'ipa':'/á/',    'sound_desc':'voice rises or stays high — marked with an acute accent (´) on the vowel',        'tiv_example':'wán',   'english_meaning':'to call'},
    {'type':'tonal',     'letter':'à  Low',   'ipa':'/à/',    'sound_desc':'voice falls or stays low — marked with a grave accent (`) on the vowel',          'tiv_example':'wàn',   'english_meaning':'to be lost'},
    {'type':'tonal',     'letter':'a  Mid',   'ipa':'/a/',    'sound_desc':'voice stays at a middle level — no accent mark written on the vowel',              'tiv_example':'wan',   'english_meaning':'word / story'},
    {'type':'tonal',     'letter':'aa Long',  'ipa':'/aː/',   'sound_desc':'vowel doubled to make a long sound — changes meaning entirely',                    'tiv_example':'vaa',   'english_meaning':'to cry / weep'},
]

# ── Main ──────────────────────────────────────────────────────────────────
if __name__ == '__main__':
    test_mode = len(sys.argv) > 1 and sys.argv[1] == 'test'
    entries   = ENTRIES[:1] if test_mode else ENTRIES

    print(f'\nTiv Alphabet Video Generator')
    print(f'Output : {OUTPUT_DIR}')
    print(f'Videos : {len(entries)} of {len(ENTRIES)}  |  {FPS}fps  {SECONDS}s  {W}×{H}')
    if test_mode:
        print('Mode   : TEST (first entry only)\n')
    else:
        print()

    ok = 0
    for i, entry in enumerate(entries, 1):
        print(f'[{i:02d}/{len(entries)}] {entry["type"].upper():10s}  {entry["letter"]}')
        if generate(entry):
            ok += 1

    print(f'\n{"="*54}')
    print(f'Done: {ok}/{len(entries)} videos  →  {OUTPUT_DIR}')
