#!/usr/bin/env python3
"""
Tiv Alphabet Group Videos — static-frame edition
Renders ONE PNG per card, ffmpeg loops + fades + adds audio.
Runs in seconds per group instead of minutes.
"""

import os, sys, subprocess, tempfile
import numpy as np
from PIL import Image, ImageDraw, ImageFont

BASE      = '/opt/lampp/htdocs/Tiv-Heritage-Archive'
OUT_DIR   = os.path.join(BASE, 'storage', 'alphabet-videos')
AUDIO_DIR = os.path.join(BASE, 'uploads', 'audio', 'alphabet')
os.makedirs(OUT_DIR, exist_ok=True)

W = H    = 1080
FPS      = 30
FADE_S   = 0.4          # fade in / fade out seconds
TITLE_S  = 3.5
MIN_S    = 3.5

BROWN      = (92,  58,  33)
DARK_BROWN = (54,  34,  19)
GOLD       = (200, 169, 81)
WHITE      = (255, 255, 255)
CREAM      = (232, 213, 176)
DIM_GOLD   = (160, 134, 60)

_F = {}
def F():
    if not _F:
        P = '/usr/share/fonts/truetype'
        _F.update({
            'big':  ImageFont.truetype(f'{P}/dejavu/DejaVuSerif-Bold.ttf', 210),
            'ipa':  ImageFont.truetype(f'{P}/dejavu/DejaVuSerif.ttf',       68),
            'desc': ImageFont.truetype(f'{P}/ubuntu/Ubuntu-R.ttf',          34),
            'ex':   ImageFont.truetype(f'{P}/ubuntu/Ubuntu-B.ttf',          56),
            'mean': ImageFont.truetype(f'{P}/ubuntu/Ubuntu-R.ttf',          38),
            'hd':   ImageFont.truetype(f'{P}/ubuntu/Ubuntu-B.ttf',          19),
            'sm':   ImageFont.truetype(f'{P}/ubuntu/Ubuntu-R.ttf',          16),
            'tc':   ImageFont.truetype(f'{P}/dejavu/DejaVuSerif-Bold.ttf', 118),
            'tcsb': ImageFont.truetype(f'{P}/ubuntu/Ubuntu-R.ttf',          26),
            'tclb': ImageFont.truetype(f'{P}/ubuntu/Ubuntu-B.ttf',          18),
        })
    return _F

CX = W // 2

def put(d, txt, x, y, font, rgb, a=255, anchor='mm'):
    d.text((x, y), txt, font=font, fill=(*rgb, a), anchor=anchor)

def wrap(d, txt, font, mx):
    words, lines, line = txt.split(), [], ''
    for w in words:
        t = (line + ' ' + w).strip()
        if d.textlength(t, font=font) > mx and line:
            lines.append(line); line = w
        else:
            line = t
    if line: lines.append(line)
    return lines

def render(ov, bg_color=BROWN):
    base = Image.new('RGB', (W, H), bg_color)
    result = Image.alpha_composite(base.convert('RGBA'), ov)
    return result.convert('RGB')

# ─────────────────────────────────────────────────────────────────────────
def make_title_png(name, count):
    f = F()
    ov = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    d  = ImageDraw.Draw(ov)

    d.rectangle([(0, 0),      (W, 10)],      fill=(*GOLD, 255))
    d.rectangle([(0, H-10),   (W, H)],        fill=(*GOLD, 255))
    d.rectangle([(0, 10),     (W, 108)],      fill=(*DARK_BROWN, 255))
    d.rectangle([(0, H-108),  (W, H-10)],     fill=(*DARK_BROWN, 255))

    put(d, 'TIV LANGUAGE ALPHABET', CX, 59,   f['tclb'], CREAM)
    put(d, 'www.tivheritage.com', CX, H-59, f['sm'], CREAM, 120)

    rw = 240
    d.line([(CX-rw, 400),(CX+rw, 400)], fill=(*DIM_GOLD, 160), width=1)
    d.line([(CX-rw, 660),(CX+rw, 660)], fill=(*DIM_GOLD, 160), width=1)

    put(d, name,  CX, 525, f['tc'],   GOLD)
    put(d, 'Tiv Heritage Archive', CX, 600, f['tcsb'], CREAM, 200)
    put(d, count, CX, 710, f['tcsb'], CREAM, 180)

    return render(ov)

def make_letter_png(e, glabel):
    f = F()
    ov = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    d  = ImageDraw.Draw(ov)

    d.rectangle([(0, 0),       (W, 108)],     fill=(*DARK_BROWN, 255))
    d.rectangle([(0, 108),     (W, 114)],     fill=(*GOLD, 255))
    d.rectangle([(0, H-114),   (W, H-108)],   fill=(*GOLD, 255))
    d.rectangle([(0, H-108),   (W, H)],       fill=(*DARK_BROWN, 255))

    put(d, 'TIV LANGUAGE ALPHABET', CX, 46,   f['hd'], CREAM)
    put(d, glabel,                   CX, 76,   f['sm'], GOLD)
    put(d, 'www.tivheritage.com', CX, H-58, f['sm'], CREAM, 100)

    put(d, e['letter'], CX, 385, f['big'], GOLD)
    put(d, e['ipa'],    CX, 535, f['ipa'], WHITE)

    rw = 460
    d.line([(CX-rw//2, 600),(CX+rw//2, 600)], fill=(*DIM_GOLD, 160), width=1)

    tmp_d = ImageDraw.Draw(Image.new('RGB', (W, H)))
    lines = wrap(tmp_d, e['sound_desc'], f['desc'], 800)
    y = 642
    for ln in lines:
        put(d, ln, CX, y, f['desc'], CREAM); y += 44

    ey = 815
    put(d, 'EXAMPLE',            CX, ey-28, f['sm'],   DIM_GOLD, 230)
    put(d, e['tiv_example'],     CX, ey+10, f['ex'],   GOLD)
    put(d, f'"{e["english_meaning"]}"', CX, ey+68, f['mean'], CREAM)

    return render(ov)

# ─────────────────────────────────────────────────────────────────────────
def audio_seconds(path):
    r = subprocess.run(
        ['ffprobe','-v','quiet','-show_entries','format=duration',
         '-of','default=noprint_wrappers=1:nokey=1', path],
        capture_output=True, text=True)
    try: return float(r.stdout.strip())
    except: return 0.0

def clip_from_png(png_path, audio_path, out_path, dur_s):
    """Loop a static PNG for dur_s, apply fade, add audio."""
    fo = max(0, dur_s - FADE_S)
    vf = f'fade=in:st=0:d={FADE_S},fade=out:st={fo:.3f}:d={FADE_S}'

    if audio_path and os.path.exists(audio_path):
        cmd = [
            'ffmpeg', '-y',
            '-loop', '1', '-framerate', str(FPS), '-i', png_path,
            '-i', audio_path,
            '-vf', vf,
            '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-crf', '18', '-preset', 'fast',
            '-c:a', 'aac', '-b:a', '128k', '-ar', '48000',
            '-af', 'apad',
            '-t', str(dur_s),
            '-movflags', '+faststart',
            out_path
        ]
    else:
        cmd = [
            'ffmpeg', '-y',
            '-loop', '1', '-framerate', str(FPS), '-i', png_path,
            '-f', 'lavfi', '-i', 'anullsrc=r=48000:cl=stereo',
            '-vf', vf,
            '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-crf', '18', '-preset', 'fast',
            '-c:a', 'aac', '-b:a', '128k', '-ar', '48000',
            '-t', str(dur_s),
            '-movflags', '+faststart',
            out_path
        ]
    subprocess.run(cmd, capture_output=True)

def concat_clips(clips, out):
    with tempfile.NamedTemporaryFile('w', suffix='.txt', delete=False) as f:
        for c in clips: f.write(f"file '{c}'\n")
        cf = f.name
    subprocess.run([
        'ffmpeg', '-y', '-f', 'concat', '-safe', '0', '-i', cf,
        '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-crf', '18', '-preset', 'fast',
        '-c:a', 'aac', '-b:a', '128k', '-ar', '48000',
        '-movflags', '+faststart', out
    ], capture_output=True)
    os.unlink(cf)

# ─────────────────────────────────────────────────────────────────────────
GROUPS = {
'plain': {
  'name':'THE ALPHABET','label':'ALPHABET','output':'group_plain.mp4',
  'entries':[
    {'letter':'A a','audio':'alpha_6a2340a9bdac4.webm'},
    {'letter':'B b','audio':'alpha_6a23531324ba8.webm'},
    {'letter':'C c','audio':'alpha_6a235337d4ffd.webm'},
    {'letter':'D d','audio':'alpha_6a235cdab7ac2.webm'},
    {'letter':'E e','audio':'alpha_6a23535ec8617.webm'},
    {'letter':'F f','audio':'alpha_6a23538261442.webm'},
    {'letter':'G g','audio':'alpha_6a2353baabf92.webm'},
    {'letter':'H h','audio':'alpha_6a2353d607e26.webm'},
    {'letter':'I i','audio':'alpha_6a2353f9c74ce.webm'},
    {'letter':'J j','audio':'alpha_6a2354150b594.webm'},
    {'letter':'K k','audio':'alpha_6a2354341d79e.webm'},
    {'letter':'L l','audio':'alpha_6a23545604284.webm'},
    {'letter':'M m','audio':'alpha_6a235471752e8.webm'},
    {'letter':'N n','audio':'alpha_6a23548e16485.webm'},
    {'letter':'O o','audio':'alpha_6a2354c0e63bf.webm'},
    {'letter':'P p','audio':'alpha_6a2354dfc3d09.webm'},
    {'letter':'R r','audio':'alpha_6a2354fd3f7a5.webm'},
    {'letter':'S s','audio':'alpha_6a2355161b5c4.webm'},
    {'letter':'T t','audio':'alpha_6a23553b40c81.webm'},
    {'letter':'U u','audio':'alpha_6a2355561d78f.webm'},
    {'letter':'V v','audio':'alpha_6a23557ea5121.webm'},
    {'letter':'W w','audio':'alpha_6a2355975308b.webm'},
    {'letter':'Y y','audio':'alpha_6a2355b09e8bf.webm'},
    {'letter':'Z z','audio':'alpha_6a2355e699286.webm'},
  ]},
'vowels': {
  'name':'VOWELS','label':'VOWEL','output':'group_vowels.mp4',
  'entries':[
    {'letter':'A a','ipa':'/a/','sound_desc':'said as "uh" — like u in sun, not a in cat','tiv_example':'bar','english_meaning':'salt','audio':'alpha_6a2343ef0a87f.webm'},
    {'letter':'E e','ipa':'/e/','sound_desc':'like e in bed or net','tiv_example':'se','english_meaning':'we / laugh','audio':'alpha_6a2344454da79.webm'},
    {'letter':'I i','ipa':'/i/','sound_desc':'like ee in feet or ea in beat','tiv_example':'ime','english_meaning':'darkness','audio':'alpha_6a23447def26e.webm'},
    {'letter':'O o','ipa':'/o/','sound_desc':'like o in orbit and story','tiv_example':'or','english_meaning':'person','audio':'alpha_6a2344bcc9912.webm'},
    {'letter':'U u','ipa':'/u/','sound_desc':'like oo in book or look','tiv_example':'bagu','english_meaning':'monkey','audio':'alpha_6a2344eb5aaa0.webm'},
  ]},
'consonants': {
  'name':'CONSONANTS','label':'CONSONANT','output':'group_consonants.mp4',
  'entries':[
    {'letter':'B b','ipa':'/b/','sound_desc':'like b in boy','tiv_example':'bam','english_meaning':'water','audio':'alpha_6a23457fd1c84.webm'},
    {'letter':'C c','ipa':'/tʃ/','sound_desc':'like ch in church — Tiv C always makes the "ch" sound','tiv_example':'cia','english_meaning':'fear','audio':'alpha_6a234ba1df080.webm'},
    {'letter':'D d','ipa':'/d/','sound_desc':'like d in dog','tiv_example':'ada','english_meaning':'bow','audio':'alpha_6a234bd561956.webm'},
    {'letter':'F f','ipa':'/f/','sound_desc':'like f in fish','tiv_example':'fa','english_meaning':'know','audio':'alpha_6a234bf17b2a3.webm'},
    {'letter':'G g','ipa':'/ɡ/','sound_desc':'like g in go','tiv_example':'gar','english_meaning':'city / town','audio':'alpha_6a234c239823e.webm'},
    {'letter':'H h','ipa':'/h/','sound_desc':'like h in hat','tiv_example':'har','english_meaning':'hang','audio':'alpha_6a234c49f1423.webm'},
    {'letter':'J j','ipa':'/dʒ/','sound_desc':'like g in gem — a soft j sound','tiv_example':'ijen','english_meaning':'hunger','audio':'alpha_6a234c9833b8c.webm'},
    {'letter':'K k','ipa':'/k/','sound_desc':'like k in key','tiv_example':'koti','english_meaning':'court','audio':'alpha_6a234cb916f2b.webm'},
    {'letter':'L l','ipa':'/l/','sound_desc':'like l in love','tiv_example':'lam','english_meaning':'speak','audio':'alpha_6a234cf7d4cab.webm'},
    {'letter':'M m','ipa':'/m/','sound_desc':'like m in man','tiv_example':'ma','english_meaning':'drink','audio':'alpha_6a234cd85ac60.webm'},
    {'letter':'N n','ipa':'/n/','sound_desc':'like n in now','tiv_example':'na','english_meaning':'give','audio':'alpha_6a234d1e3fd4d.webm'},
    {'letter':'P p','ipa':'/p/','sound_desc':'like p in pen','tiv_example':'per','english_meaning':'cross','audio':'alpha_6a234d4d4af06.webm'},
    {'letter':'R r','ipa':'/r/','sound_desc':'a rounded r — roll it slightly as in Irish English','tiv_example':'ruam','english_meaning':'fufu / pounded yam','audio':'alpha_6a234d6be076a.webm'},
    {'letter':'S s','ipa':'/s/','sound_desc':'like s in sun','tiv_example':'sar','english_meaning':'spread','audio':'alpha_6a234d8da85af.webm'},
    {'letter':'T t','ipa':'/t/','sound_desc':'like t in top','tiv_example':'ter','english_meaning':'father','audio':'alpha_6a234dad0956a.webm'},
    {'letter':'V v','ipa':'/v/','sound_desc':'like v in van','tiv_example':'ivo','english_meaning':'goat','audio':'alpha_6a234dcdb3fcb.webm'},
    {'letter':'W w','ipa':'/w/','sound_desc':'like w in water','tiv_example':'wase','english_meaning':'help','audio':'alpha_6a234dee3426b.webm'},
    {'letter':'Y y','ipa':'/j/','sound_desc':'like y in yes','tiv_example':'ya','english_meaning':'eat','audio':'alpha_6a234e0f50e02.webm'},
    {'letter':'Z z','ipa':'/z/','sound_desc':'like z in zebra — polite request form','tiv_example':'za','english_meaning':'go (request)','audio':'alpha_6a234e2b23fb4.webm'},
  ]},
'digraphs': {
  'name':'DIGRAPHS','label':'DIGRAPH','output':'group_digraphs.mp4',
  'entries':[
    {'letter':'GB gb','ipa':'/ɡ͡b/','sound_desc':'labial-velar stop — g and b said together as one sound','tiv_example':'gba','english_meaning':'fall','audio':'alpha_6a234e568f381.webm'},
    {'letter':'KP kp','ipa':'/k͡p/','sound_desc':'labial-velar stop — k and p said together as one sound','tiv_example':'kper','english_meaning':'net / tomorrow','audio':'alpha_6a234e90258cf.webm'},
    {'letter':'CH ch','ipa':'/tʃ/','sound_desc':'like ch in church — also written as C alone in some words','tiv_example':'chia','english_meaning':'fear','audio':'alpha_6a234eb72c2ba.webm'},
    {'letter':'SH sh','ipa':'/ʃ/','sound_desc':'like sh in shoe or ship','tiv_example':'sha','english_meaning':'up / away','audio':'alpha_6a234ede417d5.webm'},
    {'letter':'NY ny','ipa':'/ɲ/','sound_desc':'palatal nasal — like ny in canyon','tiv_example':'nyam','english_meaning':'meat','audio':'alpha_6a234f2aaede4.webm'},
    {'letter':'GH gh','ipa':'/ɣ/','sound_desc':'comes at end of words — silent like gh in dough','tiv_example':'sugh','english_meaning':'thank','audio':'alpha_6a234f4d7c2aa.webm'},
    {'letter':'GW gw','ipa':'/ɡw/','sound_desc':'g and w blended together — like gw in Gwen','tiv_example':'gwa','english_meaning':'fame','audio':'alpha_6a234f6cd4340.webm'},
    {'letter':'KW kw','ipa':'/kw/','sound_desc':'like qu in queen or kw in awkward','tiv_example':'kwase','english_meaning':'woman','audio':'alpha_6a234f87c1b9a.webm'},
    {'letter':'TS ts','ipa':'/ts/','sound_desc':'like ts in cats or fits','tiv_example':'tsar','english_meaning':'bridge','audio':'alpha_6a234fa7adcf4.webm'},
    {'letter':'MB mb','ipa':'/ᵐb/','sound_desc':'prenasalized b — hum through the nose before the b','tiv_example':'mban','english_meaning':'night','audio':'alpha_6a2350139678a.webm'},
    {'letter':'ND nd','ipa':'/ⁿd/','sound_desc':'prenasalized d — nasal onset before the d','tiv_example':'nder','english_meaning':'wall','audio':'alpha_6a235093deabe.webm'},
    {'letter':'NG ng','ipa':'/ŋ/','sound_desc':'nasal — like ng in sing or ring','tiv_example':'nger','english_meaning':'goat','audio':'alpha_6a2350ba6e9e5.webm'},
    {'letter':'BW bw','ipa':'/bw/','sound_desc':'b and w blended — lips round into the b','tiv_example':'bwagi','english_meaning':'pick / digging tool','audio':'alpha_6a2351135d01e.webm'},
  ]},
'tonals': {
  'name':'TONAL MARKS','label':'TONAL','output':'group_tonals.mp4',
  'entries':[
    {'letter':'á  High','ipa':'/á/','sound_desc':'voice rises or stays high — marked with acute accent (´) on the vowel','tiv_example':'wán','english_meaning':'to call','audio':None},
    {'letter':'à  Low', 'ipa':'/à/','sound_desc':'voice falls or stays low — marked with grave accent (`) on the vowel','tiv_example':'wàn','english_meaning':'to be lost','audio':None},
    {'letter':'a  Mid', 'ipa':'/a/','sound_desc':'voice stays at a middle level — no accent mark on the vowel','tiv_example':'wan','english_meaning':'word / story','audio':None},
    {'letter':'aa Long','ipa':'/aː/','sound_desc':'vowel doubled to make a long sound — changes meaning entirely','tiv_example':'vaa','english_meaning':'to cry / weep','audio':None},
  ]},
}

def make_plain_png(e, glabel):
    """Simpler card for plain letters — no IPA/desc/example."""
    f = F()
    ov = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    d  = ImageDraw.Draw(ov)

    d.rectangle([(0, 0),      (W, 108)],    fill=(*DARK_BROWN, 255))
    d.rectangle([(0, 108),    (W, 114)],    fill=(*GOLD, 255))
    d.rectangle([(0, H-114),  (W, H-108)],  fill=(*GOLD, 255))
    d.rectangle([(0, H-108),  (W, H)],      fill=(*DARK_BROWN, 255))

    put(d, 'TIV LANGUAGE ALPHABET', CX, 46, f['hd'], CREAM)
    put(d, glabel,                   CX, 76, f['sm'], GOLD)
    put(d, 'www.tivheritage.com', CX, H-58, f['sm'], CREAM, 100)

    # Large letter centred — use a bigger font for plain letters
    big = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf', 310)
    put(d, e['letter'], CX, 520, big, GOLD)

    rw = 320
    d.line([(CX-rw, 720),(CX+rw, 720)], fill=(*DIM_GOLD, 140), width=1)
    put(d, 'Tiv Heritage Archive', CX, 760, f['tcsb'], CREAM, 160)

    return render(ov)

def run_group(key):
    g = GROUPS[key]
    entries = g['entries']
    final   = os.path.join(OUT_DIR, g['output'])
    is_plain = key == 'plain'
    noun    = 'Letters' if is_plain else ('Tone Patterns' if key == 'tonals' else 'Sounds')
    count   = f"{len(entries)} {noun}"
    print(f'[{g["name"]}]', flush=True)

    with tempfile.TemporaryDirectory() as tmp:
        clips = []

        # Title card
        print(f'  title...', end=' ', flush=True)
        tc_png = os.path.join(tmp, 'title.png')
        make_title_png(g['name'], count).save(tc_png)
        tc_mp4 = os.path.join(tmp, 'clip_000.mp4')
        clip_from_png(tc_png, None, tc_mp4, TITLE_S)
        clips.append(tc_mp4)
        print('done', flush=True)

        for i, e in enumerate(entries, 1):
            ap  = os.path.join(AUDIO_DIR, e['audio']) if e['audio'] else None
            dur = audio_seconds(ap) if (ap and os.path.exists(ap)) else 0
            sec = max(MIN_S, dur + 0.8)
            mark = '♪' if dur else '◌'
            print(f'  {mark} {e["letter"]:9s}...', end=' ', flush=True)

            png = os.path.join(tmp, f'card_{i:03d}.png')
            mp4 = os.path.join(tmp, f'clip_{i:03d}.mp4')
            if is_plain:
                make_plain_png(e, g['label']).save(png)
            else:
                make_letter_png(e, g['label']).save(png)
            clip_from_png(png, ap, mp4, sec)
            clips.append(mp4)
            print('done', flush=True)

        print(f'  joining {len(clips)} clips...', end=' ', flush=True)
        concat_clips(clips, final)
        print('done', flush=True)

    mb = os.path.getsize(final) / 1024 / 1024
    print(f'  → {g["output"]}  ({mb:.1f} MB)\n', flush=True)

if __name__ == '__main__':
    target = sys.argv[1].lower() if len(sys.argv) > 1 else 'all'
    keys   = [target] if target in GROUPS else list(GROUPS.keys())
    print(f'Tiv Alphabet Videos — {len(keys)} group(s)\n')
    for k in keys:
        run_group(k)
    print('All done.')
