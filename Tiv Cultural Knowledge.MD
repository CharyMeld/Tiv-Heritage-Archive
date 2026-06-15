Tiv Cultural Knowledge Graph Implementation Guide
1️⃣ Concept

Create a Tiv Cultural Knowledge Graph where cultural items (proverbs, foods, festivals, plants, names, words) are connected instead of isolated.

Example relationships:

Proverb → Related to → Festival

Food → Used in → Festival

Plant → Used for → Food

Word → Appears in → Proverb

Name → Derived from → Word

User Experience:
A user reading a plant entry can see related foods, then related festivals, then referenced proverbs. This makes the archive interactive and interconnected.

2️⃣ Core Database Tables

You already have content tables (tiv_proverbs, tiv_foods, tiv_plants, tiv_festivals, tiv_words, tiv_names). Add two new core tables.

Table A: sources

Stores references for content.

CREATE TABLE sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_type ENUM(
        'oral_tradition',
        'book',
        'research',
        'interview',
        'community_submission'
    ),
    title VARCHAR(255),
    author VARCHAR(255),
    contributor_name VARCHAR(255),
    location VARCHAR(255),
    year_recorded INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Example entries:

id	type	contributor
1	oral_tradition	Chief Iorbee
2	book	Dr. Tor
3	interview	Elder Terhide
Table B: knowledge_links

This table powers the graph connections between items.

CREATE TABLE knowledge_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_table VARCHAR(50),
    source_id INT,
    target_table VARCHAR(50),
    target_id INT,
    relation_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Example entries:

source_table	source_id	relation_type	target_table	target_id
tiv_proverbs	12	mentions_food	tiv_foods	3
tiv_plants	4	ingredient_for	tiv_foods	3
tiv_festivals	2	uses_food	tiv_foods	3
3️⃣ Add Source Reference to Existing Tables

Add a source_id column to track where the content comes from.

ALTER TABLE tiv_proverbs ADD COLUMN source_id INT;
ALTER TABLE tiv_foods ADD COLUMN source_id INT;
ALTER TABLE tiv_plants ADD COLUMN source_id INT;
ALTER TABLE tiv_festivals ADD COLUMN source_id INT;
ALTER TABLE tiv_words ADD COLUMN source_id INT;
ALTER TABLE tiv_names ADD COLUMN source_id INT;
4️⃣ Display References on Content Pages

Example Proverb Page:

Proverb: Kwase u a lu kpa laa

Translation: A hardworking woman will succeed.

Meaning: Encourages diligence.

Source: Oral tradition recorded from Chief Iorbee Akper, Makurdi, Benue State, 2024

SQL to fetch:

SELECT p.*, s.contributor_name, s.location, s.year_recorded
FROM tiv_proverbs p
LEFT JOIN sources s ON p.source_id = s.id
WHERE p.id = ?;
5️⃣ Display Related Cultural Knowledge

Example Plant Page: Igyô

Used in Foods: Pounded Yam Soup, Vegetable Soup

Used During Festivals: Kwagh-Hir

SQL to fetch links:

SELECT *
FROM knowledge_links
WHERE source_table='tiv_plants'
AND source_id=?;

Then fetch target records (foods, festivals, proverbs) based on the link.

6️⃣ UI Section: Related Knowledge

Every content page should show related items dynamically:

Example UI layout:

Related Cultural Knowledge

Foods using this plant: Pounded Yam Soup

Festivals where this plant is used: Kwagh-Hir Festival

Proverbs mentioning this plant: "..."

This makes the archive feel alive and interconnected.

7️⃣ Contributor & Reference Page

Create /references page listing all sources.

Layout example:

Books

Tiv Proverbs and Wisdom – Dr. J.T. Tor

Oral Traditions

Chief Iorbee Akper (Makurdi)

Interviews

Elder Terhide Ugbede

SQL:

SELECT * FROM sources ORDER BY source_type;
8️⃣ Contributor Recognition Page

Create /contributors page to acknowledge contributors.

Example layout:

Community Contributors

Chief Iorbee Akper – Oral historian

Mrs. Ager Ityough – Tiv proverbs contributor

Dr. Terfa Hemba – Plant knowledge researcher

9️⃣ Admin Interface for Graph Links

In the admin dashboard, allow adding knowledge graph links:

Fields:

Source Item: [Dropdown]

Relation Type: [Dropdown]

Target Item: [Dropdown]

Saving writes into knowledge_links.

🔟 Future Upgrade: Visual Knowledge Map

Use a JS library (D3.js, Cytoscape.js, Vis.js) to display the knowledge graph visually.

Already supported by knowledge_links.

1️⃣1️⃣ Why This is Powerful

Makes the archive interconnected, research-friendly, culturally contextual, and expandable.

Turns isolated items into a Tiv Cultural Knowledge Network.

1️⃣2️⃣ Example User Journey

User reads a proverb → sees a related food → sees the festival → sees the plant used → gains deep cultural understanding.
