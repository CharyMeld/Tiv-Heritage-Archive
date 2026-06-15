🧾 TEAM SECTION – DEVELOPER IMPLEMENTATION GUIDE
🔷 1. OBJECTIVE

Create a modern, interactive “Team Section” for the Tiv Archive App that:

Displays core team members

Uses background image cards

Includes hover animations

Opens a profile modal on click

Feels like a premium SaaS product

🔷 2. SECTION STRUCTURE
📍 Placement

Add to:

Homepage (recommended), OR

/team page, OR

About page

📦 Section Layout

Use a responsive grid system

Desktop: 3 cards per row

Tablet: 2 cards

Mobile: 1 card

🔷 3. TEAM CARD DESIGN (CRITICAL)
🎯 Card Requirements

Each team member must be displayed as a background image card with overlay.

✅ Card Elements

Each card must include:

Background Image (team member photo)

Gradient Overlay (for readability)

Name

Role

Short Bio (hidden until hover)

“View Profile” button (hidden until hover)

🎨 Design Behavior
Default State:

Show:

Name

Role

Hover State:

Image zooms slightly

Content slides upward

Bio fades in

Button appears

💻 Exact UI Behavior

Card height: fixed (e.g. 350px–400px)

Border radius: large (rounded-2xl or 3xl)

Overflow: hidden

Smooth transitions (0.3s–0.7s)

🎯 Overlay Requirement (VERY IMPORTANT)

Use gradient:

Bottom: dark (black/80 or higher)

Top: transparent

Reason:

Ensures text is always readable

🔷 4. INTERACTIVITY
🖱️ Hover Effects

Must include:

Image scale (zoom effect)

Text slide-up animation

Opacity transition for hidden content

🖱️ Click Action

When user clicks a card:

👉 Open a modal popup

🔷 5. PROFILE MODAL (MANDATORY)
📦 Modal Content

Each modal must include:

Large profile image

Full Name

Role

Full Bio (longer description)

Contribution summary (important)

Social links (optional)

✨ Example Contributions

“Added 120 Proverbs”

“Led System Development”

“Cultural Research Lead”

🎯 Modal Behavior

Centered on screen

Dark background overlay

Close button (top right)

Smooth fade/scale animation

🔷 6. ANIMATIONS (IMPORTANT FOR PREMIUM FEEL)
✅ Scroll Animation

Cards should:

Fade in

Slide upward

Use:

Intersection Observer OR AOS library

✅ Hover Animation

Smooth easing (ease-in-out)

Duration: 300ms–700ms

🔷 7. DARK MODE SUPPORT

Ensure:

Overlay becomes deeper in dark mode

Text remains readable

Modal adapts to dark background

🔷 8. DATABASE STRUCTURE

Create table:

team_members
-------------
id
name
role
short_bio
full_bio
image
contributions
facebook_link
twitter_link
linkedin_link
github_link
created_at
🔷 9. ADMIN DASHBOARD FEATURE

Add a new section:

🛠 “Manage Team Members”

Admin must be able to:

Add new member

Upload image

Edit details

Delete member

🔷 10. OPTIONAL ADVANCED FEATURES (RECOMMENDED)
🌟 Filter System

All

Developers

Researchers

Contributors

🌟 Search

Search by name or role

🌟 Knowledge Graph Integration

Each team member can be linked to:

Proverbs

Foods

Festivals

Names

🔷 11. PERFORMANCE NOTES

Optimize images (compressed)

Lazy load images

Use proper caching

🔷 12. FINAL EXPECTATION

Tell your developer this clearly:

The team section must not look basic. It should feel like a modern product UI with smooth animations, layered design, and strong visual hierarchy. The background image card system with overlay and modal interaction is mandatory.
