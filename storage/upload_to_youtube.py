#!/usr/bin/env python3
"""
Tiv Heritage Archive — YouTube Uploader
Uploads all 5 alphabet group videos to YouTube.

SETUP (one-time):
  1. Go to https://console.cloud.google.com/
  2. Create a project → Enable "YouTube Data API v3"
  3. APIs & Services → Credentials → Create OAuth 2.0 Client ID
     • Application type: Desktop app
     • Name: Tiv Archive Uploader
  4. Download the JSON → save as client_secrets.json in this directory
  5. Run:  python3 upload_to_youtube.py

First run opens a browser to authorize. Token is saved locally so
subsequent uploads don't need re-authorization.
"""

import os, sys, time, pickle
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request

SCOPES          = ['https://www.googleapis.com/auth/youtube.upload']
CLIENT_SECRETS  = os.path.join(os.path.dirname(__file__), 'client_secrets.json')
TOKEN_FILE      = os.path.join(os.path.dirname(__file__), 'youtube_token.pickle')
VIDEOS_DIR      = os.path.join(os.path.dirname(__file__), 'alphabet-videos')

# ── Video metadata ─────────────────────────────────────────────────────────
VIDEOS = [
    {
        'file':        'group_plain.mp4',
        'title':       'Tiv Language Alphabet A–Z | Learn to Pronounce Every Letter',
        'description': (
            'Learn all 24 letters of the Tiv alphabet with correct pronunciation.\n\n'
            'This video covers every letter (A–Z, excluding Q and X which are not '
            'in the Tiv alphabet) with native-speaker pronunciation audio.\n\n'
            'Tiv is a Benue-Congo language spoken by the Tiv people of Central Nigeria.\n\n'
            '📚 Learn more: https://www.tivheritage.com\n\n'
            '#TivLanguage #TivAlphabet #LearnTiv #NigerianLanguage #TivCulture #Africa'
        ),
        'tags': ['Tiv language', 'Tiv alphabet', 'learn Tiv', 'Nigerian language',
                 'Tiv pronunciation', 'Tiv culture', 'Benue', 'African language',
                 'language learning', 'Tiv people'],
        'playlist_note': 'Tiv Language Alphabet Series',
    },
    {
        'file':        'group_vowels.mp4',
        'title':       'Tiv Language Vowels | A E I O U Pronunciation Guide',
        'description': (
            'Learn the 5 vowel sounds of the Tiv language with IPA symbols, '
            'sound descriptions, and example words.\n\n'
            '• A /a/ — like u in sun\n'
            '• E /e/ — like e in bed\n'
            '• I /i/ — like ee in feet\n'
            '• O /o/ — like o in orbit\n'
            '• U /u/ — like oo in book\n\n'
            '📚 Full archive: https://www.tivheritage.com\n\n'
            '#TivLanguage #TivVowels #LearnTiv #NigerianLanguage #TivPronunciation'
        ),
        'tags': ['Tiv vowels', 'Tiv language', 'learn Tiv', 'Tiv pronunciation',
                 'Nigerian language', 'Tiv culture', 'language learning', 'IPA',
                 'African language', 'Benue Nigeria'],
        'playlist_note': 'Tiv Language Alphabet Series',
    },
    {
        'file':        'group_consonants.mp4',
        'title':       'Tiv Language Consonants | All 19 Sounds with Pronunciation',
        'description': (
            'Learn all 19 consonants of the Tiv language with IPA symbols, '
            'pronunciation guides, and example Tiv words.\n\n'
            'Covers: B C D F G H J K L M N P R S T V W Y Z\n\n'
            'Each consonant is shown with:\n'
            '✅ IPA phonetic symbol\n'
            '✅ English sound comparison\n'
            '✅ Tiv example word and meaning\n'
            '✅ Native pronunciation audio\n\n'
            '📚 Full archive: https://www.tivheritage.com\n\n'
            '#TivLanguage #TivConsonants #LearnTiv #NigerianLanguage #TivPronunciation'
        ),
        'tags': ['Tiv consonants', 'Tiv language', 'learn Tiv', 'Tiv pronunciation',
                 'Nigerian language', 'Tiv culture', 'language learning', 'IPA',
                 'African language', 'Benue Nigeria'],
        'playlist_note': 'Tiv Language Alphabet Series',
    },
    {
        'file':        'group_digraphs.mp4',
        'title':       'Tiv Language Digraphs | GB KP CH SH NY and More',
        'description': (
            'Learn the 13 digraphs (two-letter sounds) unique to the Tiv language.\n\n'
            'Covers: GB, KP, CH, SH, NY, GH, GW, KW, TS, MB, ND, NG, BW\n\n'
            'Digraphs are a special feature of the Tiv writing system — two letters '
            'that combine to make a single unique sound, including rare labial-velar '
            'stops (GB, KP) found in West African languages.\n\n'
            'Each digraph includes IPA symbol, sound explanation, and a native '
            'Tiv example word.\n\n'
            '📚 Full archive: https://www.tivheritage.com\n\n'
            '#TivLanguage #TivDigraphs #LearnTiv #NigerianLanguage #TivPronunciation #Linguistics'
        ),
        'tags': ['Tiv digraphs', 'Tiv language', 'learn Tiv', 'Tiv pronunciation',
                 'Nigerian language', 'Tiv culture', 'language learning', 'IPA',
                 'African language', 'labial velar', 'GB sound', 'KP sound'],
        'playlist_note': 'Tiv Language Alphabet Series',
    },
    {
        'file':        'group_tonals.mp4',
        'title':       'Tiv Language Tonal Marks | High Low Mid Long Tones Explained',
        'description': (
            'Tiv is a tonal language — the same word can have completely different '
            'meanings depending on which tone you use.\n\n'
            'This video explains all 4 tonal patterns:\n'
            '• á High tone — voice rises or stays high\n'
            '• à Low tone — voice falls or stays low\n'
            '• a Mid tone — voice stays at a middle level (no accent mark)\n'
            '• aa Long tone — vowel doubled, changes meaning entirely\n\n'
            'Example: wán (to call), wàn (to be lost), wan (word/story) — '
            'same letters, three different meanings!\n\n'
            '📚 Full archive: https://www.tivheritage.com\n\n'
            '#TivLanguage #TonalLanguage #LearnTiv #NigerianLanguage #Linguistics #TivCulture'
        ),
        'tags': ['Tiv tones', 'tonal language', 'Tiv language', 'learn Tiv',
                 'Tiv pronunciation', 'Nigerian language', 'linguistics',
                 'African language', 'Tiv culture', 'tone marks'],
        'playlist_note': 'Tiv Language Alphabet Series',
    },
]

# ── Auth ───────────────────────────────────────────────────────────────────
def get_authenticated_service():
    creds = None

    if os.path.exists(TOKEN_FILE):
        with open(TOKEN_FILE, 'rb') as f:
            creds = pickle.load(f)

    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            if not os.path.exists(CLIENT_SECRETS):
                print('\n❌  client_secrets.json not found.')
                print('    Follow the SETUP steps at the top of this script.\n')
                sys.exit(1)
            flow = InstalledAppFlow.from_client_secrets_file(CLIENT_SECRETS, SCOPES)
            creds = flow.run_local_server(port=0)

        with open(TOKEN_FILE, 'wb') as f:
            pickle.dump(creds, f)

    return build('youtube', 'v3', credentials=creds)

# ── Upload one video ───────────────────────────────────────────────────────
def upload_video(youtube, video_meta):
    path = os.path.join(VIDEOS_DIR, video_meta['file'])
    if not os.path.exists(path):
        print(f'  ✗  File not found: {path}')
        return None

    size_mb = os.path.getsize(path) / 1024 / 1024
    print(f'\n  Uploading: {video_meta["file"]}  ({size_mb:.1f} MB)')
    print(f'  Title:     {video_meta["title"]}')

    body = {
        'snippet': {
            'title':       video_meta['title'],
            'description': video_meta['description'],
            'tags':        video_meta['tags'],
            'categoryId':  '27',   # 27 = Education
        },
        'status': {
            'privacyStatus': 'public',
            'selfDeclaredMadeForKids': False,
        }
    }

    media = MediaFileUpload(path, chunksize=1024*1024, resumable=True,
                            mimetype='video/mp4')
    request = youtube.videos().insert(part='snippet,status', body=body, media_body=media)

    response = None
    while response is None:
        status, response = request.next_chunk()
        if status:
            pct = int(status.progress() * 100)
            print(f'  Progress: {pct}%', end='\r')

    video_id = response['id']
    print(f'  ✓  Done  →  https://www.youtube.com/watch?v={video_id}')
    return video_id

# ── Main ───────────────────────────────────────────────────────────────────
if __name__ == '__main__':
    print('\nTiv Heritage Archive — YouTube Uploader')
    print('='*50)

    youtube = get_authenticated_service()

    uploaded = []
    for meta in VIDEOS:
        vid_id = upload_video(youtube, meta)
        if vid_id:
            uploaded.append({'title': meta['title'], 'id': vid_id,
                             'url': f'https://www.youtube.com/watch?v={vid_id}'})
        time.sleep(2)   # brief pause between uploads

    print('\n' + '='*50)
    print(f'Uploaded {len(uploaded)}/{len(VIDEOS)} videos:\n')
    for v in uploaded:
        print(f'  {v["title"]}')
        print(f'  {v["url"]}\n')
