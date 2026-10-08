<?php
/**
 * HeritageRegistry — field definitions for the Nigeria Heritage knowledge
 * tables (database/migrations/nigeria_expansion_001.sql). The admin module
 * (AdminHeritageController) builds its lists and forms from these, so the
 * enum options here must match the migration.
 *
 * Field keys: label, type (text|textarea|number|date|select|fk|checkbox),
 * options (select), fk (entity key or 'sources'), required, help, min/max (number).
 * Entity flags: no_slug, no_knowledge (no sources/relations panel), no_dashboard (not in the
 * dashboard's evidence lists), readonly, panels (extra child-row panels: claim_sources,
 * attributes, media_links), source_count ([table, fk] counting sources for evidence rules),
 * refs ([table col, id col, required] record references to check), set_user (column set to
 * the current user on create), publish_requires, rules (if … require / when [field, value] require).
 */
class HeritageRegistry
{
    public const EVIDENCE = [
        'verified' => 'Verified', 'well_documented' => 'Well documented', 'multiple_sources' => 'Multiple sources',
        'single_reliable_source' => 'Single reliable source', 'community_source' => 'Community source',
        'oral_tradition' => 'Oral tradition', 'scholarly_interpretation' => 'Scholarly interpretation',
        'disputed' => 'Disputed', 'needs_corroboration' => 'Needs corroboration', 'unverified' => 'Unverified',
        'outdated' => 'Outdated',
    ];
    /** Evidence statuses that flag a record for research attention on the dashboard. */
    public const EVIDENCE_ATTENTION = ['disputed', 'needs_corroboration', 'unverified', 'outdated'];

    public const REVIEW = ['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'];
    public const PRECISION = [
        'unknown' => 'Unknown', 'exact' => 'Exact date', 'month' => 'Month', 'year' => 'Year', 'circa' => 'Circa',
        'decade' => 'Decade', 'century' => 'Century', 'range' => 'Range',
    ];
    public const TEMPORAL = [
        'historical' => 'Historical', 'current' => 'Current', 'announced' => 'Announced', 'planned' => 'Planned',
        'proposed' => 'Proposed', 'projected' => 'Projected', 'forecast' => 'Forecast',
    ];

    /* Controlled vocabularies of migration 002 (NIGERIA_STEP5_CONTROLLED_VOCABULARIES.md). */

    /** The brief's six evidence levels (Step 5 §1.1). evidence_status is kept alongside. */
    public const EVIDENCE_LEVEL = [
        'verified' => 'Verified', 'well_documented' => 'Well documented', 'reported' => 'Reported',
        'needs_corroboration' => 'Needs corroboration', 'disputed' => 'Disputed', 'uncertain' => 'Uncertain',
    ];
    public const EVIDENCE_LEVEL_HELP = 'Verified: a Tier 1 source, or two independent Tier 1–3 sources. Well documented: a Tier 1–2 source. '
        . 'Reported: a Tier 3–4 source. Needs corroboration: weak or thin evidence. Disputed: sources disagree (record the dispute). Uncertain: no usable source yet.';
    public const SENSITIVITY = [
        'public' => 'Public', 'community_sensitive' => 'Community sensitive', 'restricted' => 'Restricted',
        'permission_required' => 'Permission required', 'unknown' => 'Unknown',
    ];
    public const RESEARCH_STATUS = [
        'not_started' => 'Not started', 'in_progress' => 'In progress', 'drafted' => 'Drafted', 'needs_review' => 'Needs review',
        'qc_passed' => 'Quality-checked', 'approved' => 'Approved', 'needs_update' => 'Needs update',
    ];
    public const SETTLEMENT = [
        'indigenous_core' => 'Indigenous (core homeland)', 'indigenous_shared' => 'Indigenous (shared area)',
        'historically_present' => 'Historically present', 'significant_contemporary' => 'Significant contemporary presence',
        'migrant_community' => 'Migrant community', 'mixed_community' => 'Mixed community', 'disputed' => 'Disputed', 'unknown' => 'Unknown',
    ];
    public const LOCATION_TYPE = [
        'core_homeland' => 'Core homeland', 'current_indigenous' => 'Current indigenous area', 'secondary_settlement' => 'Secondary settlement',
        'urban_contemporary' => 'Urban (contemporary)', 'historical_presence' => 'Historical presence',
    ];
    public const SPEAKER_ROLE = [
        'first_language' => 'First language', 'second_language' => 'Second language', 'lingua_franca' => 'Lingua franca',
        'heritage_language' => 'Heritage language', 'unknown' => 'Unknown',
    ];
    /** Source types (Step 5 §4.1) with their default tier. */
    public const SOURCE_KIND = [
        'legislation' => ['Legislation (constitution, acts, gazettes)', 1], 'government_publication' => ['Government publication', 1],
        'census_statistics' => ['Census / statistics (NPC, NBS)', 1], 'official_website' => ['Official government website', 1],
        'heritage_body' => ['Heritage body (NCMM, museums, UNESCO)', 1], 'archival_record' => ['Archival record', 1],
        'journal_article' => ['Journal article (peer-reviewed)', 2], 'academic_book' => ['Academic book', 2], 'thesis' => ['Thesis', 2],
        'linguistic_database' => ['Linguistic database (Glottolog, Ethnologue)', 2], 'research_report' => ['Research report', 2],
        'encyclopedia' => ['Encyclopedia', 3], 'reference_database' => ['Reference compilation (Statoids, City Population)', 3],
        'news' => ['News', 3], 'general_book' => ['General book', 3],
        'community_organisation' => ['Community organisation', 4], 'oral_history' => ['Oral history / interview', 4],
        'community_submission' => ['Community submission', 4], 'website' => ['General website / blog', 5],
        'map' => ['Map (tier by publisher)', null], 'other' => ['Other', 5],
    ];
    public const SOURCE_TIER = [1 => 'Tier 1 — official', 2 => 'Tier 2 — academic', 3 => 'Tier 3 — reference / news',
                                4 => 'Tier 4 — community', 5 => 'Tier 5 — general web'];

    /** Cultural record detail kinds (cultural_record_attributes.attribute). */
    public const CULTURAL_ATTRIBUTES = [
        'ingredient' => 'Ingredient', 'preparation_step' => 'Preparation step', 'material' => 'Material', 'garment' => 'Garment',
        'instrument' => 'Instrument', 'occasion' => 'Occasion', 'performer' => 'Performer', 'season' => 'Season',
        'craft_type' => 'Craft type', 'occupation_type' => 'Occupation type', 'local_term' => 'Local term',
    ];

    /** Tables that can be the subject/object of a relation, name, statistic or source link. */
    public const LINKABLE = [
        'admin_units' => 'Administrative unit', 'places' => 'Place', 'ethnic_groups' => 'Ethnic group',
        'languages' => 'Language', 'polities' => 'Kingdom / institution', 'cultural_records' => 'Cultural record',
        'historical_periods' => 'Historical period', 'historical_figures' => 'Person',
        'timeline_events' => 'Event', 'content_items' => 'Article / document', 'tiv_festivals' => 'Tiv festival',
        'tiv_foods' => 'Tiv food', 'communities' => 'Community', 'oral_histories' => 'Oral history',
    ];
    /** Display-name column per linkable table. */
    public const NAME_COLUMN = [
        'admin_units' => 'name', 'places' => 'name', 'ethnic_groups' => 'name', 'languages' => 'name',
        'polities' => 'name', 'cultural_records' => 'name', 'historical_periods' => 'name',
        'historical_figures' => 'english_name', 'timeline_events' => 'title', 'content_items' => 'title',
        'tiv_festivals' => 'tiv_name', 'tiv_foods' => 'tiv_name', 'communities' => 'name', 'oral_histories' => 'title',
    ];

    public static function entities(): array
    {
        $common = [
            'summary'         => ['label' => 'Summary', 'type' => 'textarea', 'rows' => 2, 'help' => 'One or two sentences, own words (max 500 characters).'],
            'description'     => ['label' => 'Description', 'type' => 'textarea', 'rows' => 8],
        ];
        $level = ['evidence_level' => ['label' => 'Evidence level (six-level scale)', 'type' => 'select', 'options' => self::EVIDENCE_LEVEL, 'help' => self::EVIDENCE_LEVEL_HELP]];
        $sens = ['sensitivity' => ['label' => 'Sensitivity', 'type' => 'select', 'options' => self::SENSITIVITY, 'required' => true, 'default' => 'public',
                                   'help' => 'Only records marked Public can be published.']];
        $rstatus = ['research_status' => ['label' => 'Research status', 'type' => 'select', 'options' => self::RESEARCH_STATUS, 'required' => true, 'default' => 'not_started']];
        $meta = [
            'evidence_status' => ['label' => 'Evidence status (earlier scale)', 'type' => 'select', 'options' => self::EVIDENCE, 'required' => true, 'default' => 'unverified',
                                  'help' => 'Kept for the public pages until they switch to the six-level scale.'],
        ] + $level + $sens + $rstatus + [
            'review_status'   => ['label' => 'Review status', 'type' => 'select', 'options' => self::REVIEW, 'required' => true, 'default' => 'draft'],
            'research_batch_id' => ['label' => 'Research batch', 'type' => 'fk', 'fk' => 'research-batches'],
        ];
        // Historical periods have no sensitivity / research status columns.
        $periodMeta = array_diff_key($meta, ['sensitivity' => 1, 'research_status' => 1]);
        $coords = fn(string $what) => [
            'latitude' => ['label' => 'Latitude', 'type' => 'number', 'step' => 'any', 'help' => "Only from a cited source — never estimated ({$what})."],
            'longitude' => ['label' => 'Longitude', 'type' => 'number', 'step' => 'any'],
            'coords_source_id' => ['label' => 'Coordinates source', 'type' => 'fk', 'fk' => 'sources'],
        ];
        $unitsFk = ['label' => 'Administrative unit', 'type' => 'fk', 'fk' => 'admin-units'];

        return [
            'admin-units' => [
                'table' => 'admin_units', 'label' => 'Administrative unit', 'plural' => 'States, LGAs & units', 'icon' => '&#127963;',
                'slug_scope' => ['parent_id', 'unit_type'],
                'list' => ['name', 'unit_type', 'parent_id', 'status', 'evidence_status', 'review_status'],
                'filter' => 'unit_type',
                'fields' => [
                    'unit_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'options' => [
                        'country' => 'Country', 'region' => 'Region', 'province' => 'Province', 'division' => 'Division',
                        'district' => 'District', 'state' => 'State', 'federal_capital_territory' => 'Federal Capital Territory',
                        'lga' => 'Local Government Area', 'other' => 'Other', 'geopolitical_zone' => 'Geopolitical zone', 'ward' => 'Ward']],
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'official_name' => ['label' => 'Official name', 'type' => 'text'],
                    'parent_id' => ['label' => 'Part of', 'type' => 'fk', 'fk' => 'admin-units', 'help' => 'Containing unit at the time it existed (e.g. the state for an LGA).'],
                    'iso_code' => ['label' => 'ISO code', 'type' => 'text', 'help' => 'e.g. NG-BE'],
                    'official_code' => ['label' => 'Official code', 'type' => 'text', 'maxlength' => 30, 'help' => 'Code from the issuing body, e.g. the INEC ward code.'],
                    'geopolitical_zone' => ['label' => 'Geopolitical zone', 'type' => 'select', 'options' => [
                        '' => '—', 'North Central' => 'North Central', 'North East' => 'North East', 'North West' => 'North West',
                        'South East' => 'South East', 'South South' => 'South South', 'South West' => 'South West']],
                    'capital_place_id' => ['label' => 'Capital', 'type' => 'fk', 'fk' => 'places'],
                    'headquarters_place_id' => ['label' => 'Headquarters (LGA)', 'type' => 'fk', 'fk' => 'places', 'help' => 'The LGA headquarters town, from a cited source.'],
                ] + $coords('a point for the unit') + [
                    'gis_status' => ['label' => 'Map status', 'type' => 'select', 'required' => true, 'default' => 'needs_gis', 'options' => [
                        'needs_gis' => 'Needs mapping', 'point_only' => 'Point only', 'boundary_available' => 'Boundary available', 'not_applicable' => 'Not applicable']],
                    'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'default' => 'current', 'options' => [
                        'current' => 'Current', 'abolished' => 'Abolished', 'renamed' => 'Renamed', 'historical' => 'Historical']],
                    'created_on' => ['label' => 'Created on', 'type' => 'date'],
                    'created_on_text' => ['label' => 'Created (as stated)', 'type' => 'text', 'help' => 'e.g. "3 February 1976"'],
                    'created_precision' => ['label' => 'Creation date precision', 'type' => 'select', 'options' => self::PRECISION, 'default' => 'unknown'],
                    'ended_on' => ['label' => 'Ended on', 'type' => 'date'],
                    'ended_on_text' => ['label' => 'Ended (as stated)', 'type' => 'text'],
                    'ended_precision' => ['label' => 'End date precision', 'type' => 'select', 'options' => self::PRECISION, 'default' => 'unknown'],
                ] + $common + [
                    'geography_notes' => ['label' => 'Geography notes', 'type' => 'textarea', 'rows' => 4],
                ] + $meta,
                'rules' => [['if' => ['latitude', 'longitude'], 'require' => 'coords_source_id', 'message' => 'Coordinates need a cited source.']],
            ],
            'places' => [
                'table' => 'places', 'label' => 'Place', 'plural' => 'Places & sites', 'icon' => '&#128205;',
                'list' => ['name', 'place_type', 'admin_unit_id', 'evidence_status', 'review_status'],
                'filter' => 'place_type',
                'fields' => [
                    'place_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'options' => [
                        'city' => 'City', 'town' => 'Town', 'village' => 'Village', 'settlement' => 'Settlement',
                        'historical_place' => 'Historical place', 'heritage_site' => 'Heritage site',
                        'archaeological_site' => 'Archaeological site', 'museum' => 'Museum', 'archive' => 'Archive',
                        'library' => 'Library', 'monument' => 'Monument', 'sacred_site' => 'Sacred site',
                        'natural_feature' => 'Natural feature', 'river' => 'River', 'hill_or_mountain' => 'Hill / mountain',
                        'institution' => 'Institution', 'other' => 'Other']],
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'admin_unit_id' => ['label' => 'Located in (unit)', 'type' => 'fk', 'fk' => 'admin-units', 'help' => 'Current unit, usually the LGA. Earlier units go in Relations ("was formerly part of").'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'default' => 'existing', 'options' => [
                        'existing' => 'Existing', 'historical' => 'Historical', 'destroyed' => 'Destroyed', 'relocated' => 'Relocated', 'unknown' => 'Unknown']],
                    'latitude' => ['label' => 'Latitude', 'type' => 'number', 'step' => 'any', 'help' => 'Only from a cited source — never estimated.'],
                    'longitude' => ['label' => 'Longitude', 'type' => 'number', 'step' => 'any'],
                    'coords_source_id' => ['label' => 'Coordinates source', 'type' => 'fk', 'fk' => 'sources'],
                    'protection_status' => ['label' => 'Protection', 'type' => 'select', 'options' => [
                        'national_monument' => 'National monument', 'unesco_world_heritage' => 'UNESCO World Heritage Site',
                        'unesco_tentative' => 'UNESCO tentative list', 'state_protected' => 'State protected', 'none_known' => 'None known']],
                    'condition_status' => ['label' => 'Condition', 'type' => 'select', 'options' => [
                        'good' => 'Good', 'fair' => 'Fair', 'threatened' => 'Threatened', 'damaged' => 'Damaged', 'destroyed' => 'Destroyed', 'unknown' => 'Unknown']],
                    'condition_as_of' => ['label' => 'Condition as of', 'type' => 'date'],
                ] + $common + [
                    'history' => ['label' => 'History', 'type' => 'textarea', 'rows' => 6],
                ] + $meta,
                'rules' => [['if' => ['latitude', 'longitude'], 'require' => 'coords_source_id', 'message' => 'Coordinates need a cited source.']],
            ],
            'ethnic-groups' => [
                'table' => 'ethnic_groups', 'label' => 'Ethnic group', 'plural' => 'Ethnic groups', 'icon' => '&#128101;',
                'list' => ['name', 'endonym', 'parent_id', 'evidence_status', 'review_status'],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'endonym' => ['label' => 'Self-designation (endonym)', 'type' => 'text', 'help' => 'Other and historical names go in "Names" after saving.'],
                    'parent_id' => ['label' => 'Sub-group of', 'type' => 'fk', 'fk' => 'ethnic-groups'],
                    'group_level' => ['label' => 'Level', 'type' => 'select', 'required' => true, 'default' => 'ethnic_group', 'options' => [
                        'ethnic_group' => 'Ethnic group', 'subgroup' => 'Subgroup', 'clan' => 'Clan', 'lineage' => 'Lineage', 'community_identity' => 'Community identity']],
                ] + $common + [
                    'classification_notes' => ['label' => 'Classification notes', 'type' => 'textarea', 'rows' => 3, 'help' => 'Where sources classify the group differently, say so here.'],
                    'history' => ['label' => 'History', 'type' => 'textarea', 'rows' => 6],
                    'origins_and_migration' => ['label' => 'Origins & migration', 'type' => 'textarea', 'rows' => 6, 'help' => 'Say explicitly when an account is oral tradition.'],
                    'traditional_governance' => ['label' => 'Traditional governance', 'type' => 'textarea', 'rows' => 4],
                    'social_organisation' => ['label' => 'Social organisation', 'type' => 'textarea', 'rows' => 4],
                    'economy_and_occupations' => ['label' => 'Economy & occupations', 'type' => 'textarea', 'rows' => 4],
                    'preservation_notes' => ['label' => 'Preservation issues', 'type' => 'textarea', 'rows' => 3],
                ] + $meta,
            ],
            'languages' => [
                'table' => 'languages', 'label' => 'Language', 'plural' => 'Languages & dialects', 'icon' => '&#128483;',
                'list' => ['name', 'lang_type', 'parent_id', 'iso639_3', 'evidence_status', 'review_status'],
                'filter' => 'lang_type',
                'fields' => [
                    'lang_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'default' => 'language', 'options' => [
                        'family' => 'Family', 'branch' => 'Branch', 'language' => 'Language', 'dialect' => 'Dialect', 'variety' => 'Variety']],
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'parent_id' => ['label' => 'Belongs to', 'type' => 'fk', 'fk' => 'languages', 'help' => 'Family/branch for a language; language for a dialect.'],
                    'iso639_3' => ['label' => 'ISO 639-3', 'type' => 'text', 'maxlength' => 3],
                    'glottocode' => ['label' => 'Glottocode', 'type' => 'text', 'maxlength' => 8],
                    'writing_system' => ['label' => 'Writing system', 'type' => 'text'],
                ] + $common + [
                    'orthography_notes' => ['label' => 'Orthography', 'type' => 'textarea', 'rows' => 3],
                    'literature_notes' => ['label' => 'Literature', 'type' => 'textarea', 'rows' => 3],
                    'educational_use' => ['label' => 'Educational use', 'type' => 'textarea', 'rows' => 3],
                    'digital_resources' => ['label' => 'Digital resources', 'type' => 'textarea', 'rows' => 3],
                    'vitality' => ['label' => 'Vitality (as the source states it)', 'type' => 'text', 'help' => 'Speaker numbers go in "Statistics", never here.'],
                    'vitality_status' => ['label' => 'Vitality (controlled)', 'type' => 'select', 'options' => [
                        'safe' => 'Safe', 'stable' => 'Stable', 'threatened' => 'Threatened', 'endangered' => 'Endangered',
                        'moribund' => 'Moribund', 'extinct' => 'Extinct', 'unknown' => 'Unknown']],
                    'vitality_source_id' => ['label' => 'Vitality source', 'type' => 'fk', 'fk' => 'sources'],
                    'documentation_notes' => ['label' => 'Documentation (dictionaries, grammars, recordings)', 'type' => 'textarea', 'rows' => 3],
                    'preservation_notes' => ['label' => 'Preservation efforts', 'type' => 'textarea', 'rows' => 3],
                ] + $meta,
                'rules' => [['if' => ['vitality', 'vitality_status'], 'require' => 'vitality_source_id', 'message' => 'Vitality needs a cited source.']],
            ],
            'polities' => [
                'table' => 'polities', 'label' => 'Kingdom / institution', 'plural' => 'Kingdoms & institutions', 'icon' => '&#128081;',
                'list' => ['name', 'polity_type', 'founded_text', 'evidence_status', 'review_status'],
                'filter' => 'polity_type',
                'fields' => [
                    'polity_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'options' => [
                        'empire' => 'Empire', 'kingdom' => 'Kingdom', 'caliphate' => 'Caliphate', 'emirate' => 'Emirate',
                        'chiefdom' => 'Chiefdom', 'confederacy' => 'Confederacy', 'city_state' => 'City-state',
                        'traditional_council' => 'Traditional council', 'traditional_title' => 'Traditional title',
                        'acephalous_society' => 'Acephalous society', 'other' => 'Other']],
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'seat_place_id' => ['label' => 'Seat / capital', 'type' => 'fk', 'fk' => 'places'],
                    'founded_year' => ['label' => 'Founded (year)', 'type' => 'number'],
                    'founded_text' => ['label' => 'Founded (as stated)', 'type' => 'text'],
                    'founded_precision' => ['label' => 'Founding precision', 'type' => 'select', 'options' => self::PRECISION, 'default' => 'unknown'],
                    'ended_year' => ['label' => 'Ended (year)', 'type' => 'number'],
                    'ended_text' => ['label' => 'Ended (as stated)', 'type' => 'text'],
                    'ended_precision' => ['label' => 'End precision', 'type' => 'select', 'options' => self::PRECISION, 'default' => 'unknown'],
                    'is_extant' => ['label' => 'Still exists?', 'type' => 'select', 'options' => ['' => 'Not established', '1' => 'Yes', '0' => 'No']],
                ] + $common + [
                    'governance' => ['label' => 'Governance', 'type' => 'textarea', 'rows' => 5],
                ] + $meta,
            ],
            'cultural-records' => [
                'table' => 'cultural_records', 'label' => 'Cultural record', 'plural' => 'Culture & heritage', 'icon' => '&#127917;',
                'panels' => ['attributes'],
                'list' => ['name', 'record_type', 'nature', 'evidence_status', 'review_status'],
                'filter' => 'record_type',
                'fields' => [
                    'record_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'options' => [
                        'festival' => 'Festival', 'food' => 'Food', 'clothing' => 'Clothing', 'music' => 'Music', 'dance' => 'Dance',
                        'art' => 'Art', 'craft' => 'Craft', 'architecture' => 'Architecture', 'naming_practice' => 'Naming practice',
                        'marriage_tradition' => 'Marriage tradition', 'burial_tradition' => 'Burial tradition',
                        'ceremony' => 'Ceremony', 'oral_tradition' => 'Oral tradition', 'folklore' => 'Folklore',
                        'proverb' => 'Proverb', 'occupation' => 'Occupation', 'agricultural_knowledge' => 'Agricultural knowledge',
                        'indigenous_knowledge' => 'Indigenous knowledge', 'game_or_sport' => 'Game / sport', 'other' => 'Other',
                        'age_grade' => 'Age grade', 'adornment' => 'Adornment', 'belief_ritual' => 'Belief / ritual']],
                    'cultural_category' => ['label' => 'Category', 'type' => 'select', 'options' => [
                        'traditional_institutions' => 'Traditional institutions', 'festivals_ceremonies' => 'Festivals & ceremonies', 'food' => 'Food',
                        'clothing_adornment' => 'Clothing & adornment', 'music' => 'Music', 'dance' => 'Dance', 'arts_crafts' => 'Arts & crafts',
                        'occupations' => 'Occupations', 'knowledge_belief' => 'Knowledge & belief', 'oral_literature' => 'Oral literature',
                        'games_sport' => 'Games & sport', 'other' => 'Other']],
                    'nature' => ['label' => 'Nature of the record', 'type' => 'select', 'required' => true, 'default' => 'documented_practice',
                        'help' => 'Oral traditions and folklore are shown as cultural records, never as proven history.', 'options' => [
                        'documented_practice' => 'Documented practice', 'contemporary_practice' => 'Contemporary practice',
                        'historical_practice' => 'Historical practice', 'oral_tradition' => 'Oral tradition',
                        'folklore' => 'Folklore', 'scholarly_interpretation' => 'Scholarly interpretation']],
                    'name' => ['label' => 'Name (English)', 'type' => 'text', 'required' => true],
                    'local_name' => ['label' => 'Local name', 'type' => 'text'],
                    'language_id' => ['label' => 'Language of local name', 'type' => 'fk', 'fk' => 'languages'],
                    'timing' => ['label' => 'When observed', 'type' => 'text', 'help' => 'Festivals / ceremonies.'],
                    'season' => ['label' => 'Season', 'type' => 'text', 'maxlength' => 100, 'help' => 'e.g. "after the yam harvest"'],
                    'month_from' => ['label' => 'Usual month (from)', 'type' => 'number', 'min' => 1, 'max' => 12, 'help' => '1–12, only when a source gives it.'],
                    'month_to' => ['label' => 'Usual month (to)', 'type' => 'number', 'min' => 1, 'max' => 12],
                    'current_status' => ['label' => 'Current status', 'type' => 'select', 'options' => [
                        'active' => 'Active', 'declining' => 'Declining', 'revived' => 'Revived', 'historical' => 'Historical', 'unknown' => 'Unknown']],
                    'scope_level' => ['label' => 'Scope', 'type' => 'select', 'options' => [
                        'community' => 'Community', 'lga' => 'LGA', 'subgroup' => 'Subgroup', 'ethnic_group' => 'Ethnic group',
                        'regional' => 'Regional', 'national' => 'National']],
                ] + $common + [
                    'significance' => ['label' => 'Significance', 'type' => 'textarea', 'rows' => 4],
                    'status_notes' => ['label' => 'Continuity / decline / revival', 'type' => 'textarea', 'rows' => 3],
                ] + $meta,
            ],
            'historical-periods' => [
                'table' => 'historical_periods', 'label' => 'Historical period', 'plural' => 'Historical periods', 'icon' => '&#9203;',
                'list' => ['name', 'scope', 'start_year', 'end_year', 'evidence_status', 'review_status'],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'scope' => ['label' => 'Scope', 'type' => 'select', 'required' => true, 'default' => 'national', 'options' => [
                        'national' => 'National', 'regional' => 'Regional', 'group' => 'Specific group']],
                    'start_year' => ['label' => 'Start year', 'type' => 'number', 'help' => 'Negative for BCE.'],
                    'end_year' => ['label' => 'End year', 'type' => 'number'],
                    'date_precision' => ['label' => 'Date precision', 'type' => 'select', 'options' => self::PRECISION, 'default' => 'year'],
                ] + $common + $periodMeta,
            ],
            // Existing people/events tables: edited in their own admin screens; here only
            // collection membership, national URL, evidence status and the knowledge panel.
            'people' => [
                'table' => 'historical_figures', 'label' => 'Person', 'plural' => 'People (collections & sources)', 'icon' => '&#129332;',
                'title_field' => 'english_name', 'readonly' => true, 'no_slug' => true,
                'list' => ['english_name', 'category', 'status'], 'fields' => [],
                'edit_url' => 'admin/historical-figures/%d/edit',
            ],
            'events' => [
                'table' => 'timeline_events', 'label' => 'Event', 'plural' => 'Events (collections & sources)', 'icon' => '&#128337;',
                'title_field' => 'title', 'readonly' => true, 'no_slug' => true,
                'list' => ['title', 'event_date', 'status'], 'fields' => [],
                // No admin editor exists for timeline events yet (they are added by seed scripts).
            ],
            'research-batches' => [
                'table' => 'research_batches', 'label' => 'Research batch', 'plural' => 'Research batches', 'icon' => '&#128269;',
                'title_field' => 'title', 'no_slug' => true, 'no_knowledge' => true,
                'list' => ['title', 'status', 'conducted_by', 'started_at', 'completed_at'],
                'filter' => 'status',
                'fields' => [
                    'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'help' => 'e.g. "Benue State — administrative history"'],
                    'scope' => ['label' => 'Scope', 'type' => 'textarea', 'rows' => 3, 'required' => true],
                    'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'default' => 'planned', 'options' => [
                        'planned' => 'Planned', 'in_progress' => 'In progress', 'awaiting_review' => 'Awaiting review',
                        'approved' => 'Approved', 'rejected' => 'Rejected']],
                    'conducted_by' => ['label' => 'Conducted by', 'type' => 'text'],
                    'started_at' => ['label' => 'Started', 'type' => 'date'],
                    'completed_at' => ['label' => 'Completed', 'type' => 'date'],
                    'summary' => ['label' => 'What was inserted', 'type' => 'textarea', 'rows' => 6],
                    'unresolved_notes' => ['label' => 'Unresolved / not inserted', 'type' => 'textarea', 'rows' => 6],
                ],
            ],
            'research-gaps' => [
                'table' => 'research_gaps', 'label' => 'Research gap', 'plural' => 'Research gaps', 'icon' => '&#10067;',
                'title_field' => 'topic', 'no_slug' => true, 'no_knowledge' => true,
                'list' => ['topic', 'status', 'research_batch_id', 'entity_table', 'entity_id'],
                'filter' => 'status',
                'fields' => [
                    'topic' => ['label' => 'Topic', 'type' => 'text', 'required' => true],
                    'description' => ['label' => 'What could not be verified, and why', 'type' => 'textarea', 'rows' => 4],
                    'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'default' => 'open', 'options' => [
                        'open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'unresolvable' => 'Unresolvable']],
                    'research_batch_id' => ['label' => 'Research batch', 'type' => 'fk', 'fk' => 'research-batches'],
                    'entity_table' => ['label' => 'Record type (optional)', 'type' => 'select', 'options' => ['' => '—'] + self::LINKABLE],
                    'entity_id' => ['label' => 'Record ID (optional)', 'type' => 'number'],
                    'admin_unit_id' => ['label' => 'State / LGA (optional)', 'type' => 'fk', 'fk' => 'admin-units'],
                    'gap_type' => ['label' => 'Kind of gap', 'type' => 'select', 'options' => [
                        'community_level_missing' => 'Community level missing', 'ward_level_missing' => 'Ward level missing',
                        'classification_disputed' => 'Classification disputed', 'language_classification_differs' => 'Language classification differs',
                        'oral_only' => 'Oral sources only', 'no_population_data' => 'No population data', 'source_conflict' => 'Sources conflict',
                        'name_variants' => 'Name variants', 'festival_status_unknown' => 'Festival status unknown',
                        'official_source_missing' => 'Official source missing', 'other' => 'Other']],
                    'why_missing' => ['label' => 'Why it is missing', 'type' => 'textarea', 'rows' => 3],
                    'recommended_research' => ['label' => 'Recommended research', 'type' => 'textarea', 'rows' => 3],
                    'priority' => ['label' => 'Priority', 'type' => 'select', 'required' => true, 'default' => 'medium', 'options' => ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low']],
                ],
            ],
            // ── Migration 002 (Step 4 schema) ──────────────────────────────
            'communities' => [
                'table' => 'communities', 'label' => 'Community', 'plural' => 'Communities', 'icon' => '&#127960;',
                'slug_scope' => ['admin_unit_id'],
                'list' => ['name', 'community_type', 'admin_unit_id', 'evidence_level', 'review_status'],
                'filter' => 'community_type',
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'community_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'options' => [
                        'clan_area' => 'Clan area', 'kindred' => 'Kindred', 'village_group' => 'Village group', 'town_quarter' => 'Town quarter',
                        'traditional_community' => 'Traditional community', 'other' => 'Other']],
                    'admin_unit_id' => ['required' => true, 'help' => 'The smallest unit the sources support (LGA or ward).'] + $unitsFk,
                    'place_id' => ['label' => 'Main settlement', 'type' => 'fk', 'fk' => 'places'],
                    'parent_community_id' => ['label' => 'Part of community', 'type' => 'fk', 'fk' => 'communities'],
                ] + $common + $meta,
            ],
            'claims' => [
                'table' => 'claims', 'label' => 'Claim', 'plural' => 'Claims', 'icon' => '&#128221;',
                'title_field' => 'statement', 'no_slug' => true, 'no_knowledge' => true, 'no_dashboard' => true,
                'panels' => ['claim_sources'], 'source_count' => ['claim_sources', 'claim_id'],
                'list' => ['statement', 'geographic_level', 'claim_nature', 'evidence_level', 'review_status'],
                'filter' => 'claim_nature',
                'refs' => [['subject_table', 'subject_id', true], ['object_table', 'object_id', false]],
                'fields' => [
                    'statement' => ['label' => 'Statement', 'type' => 'text', 'required' => true, 'maxlength' => 500, 'help' => 'One sentence stating the claim, in neutral words.'],
                    'subject_table' => ['label' => 'About (record type)', 'type' => 'select', 'required' => true, 'options' => self::LINKABLE],
                    'subject_id' => ['label' => 'About (record ID)', 'type' => 'number', 'required' => true],
                    'predicate' => ['label' => 'What is claimed', 'type' => 'text', 'required' => true, 'maxlength' => 60, 'help' => 'e.g. present_in, founded_in, population, capital'],
                    'object_table' => ['label' => 'Related record type (optional)', 'type' => 'select', 'options' => ['' => '—'] + self::LINKABLE],
                    'object_id' => ['label' => 'Related record ID', 'type' => 'number'],
                    'value_text' => ['label' => 'Value (text)', 'type' => 'text'],
                    'value_number' => ['label' => 'Value (number)', 'type' => 'number', 'step' => 'any'],
                    'value_date' => ['label' => 'Value (date as stated)', 'type' => 'text', 'maxlength' => 40],
                    'geographic_level' => ['label' => 'Geographic level', 'type' => 'select', 'required' => true, 'options' => [
                        'national' => 'National', 'zone' => 'Zone', 'state' => 'State', 'lga' => 'LGA', 'ward' => 'Ward', 'community' => 'Community', 'site' => 'Site']],
                    'claim_nature' => ['label' => 'Nature of the claim', 'type' => 'select', 'required' => true, 'options' => [
                        'documented_fact' => 'Documented fact', 'government_documentation' => 'Government documentation', 'archival_evidence' => 'Archival evidence',
                        'archaeological_evidence' => 'Archaeological evidence', 'recorded_historical_account' => 'Recorded historical account',
                        'academic_interpretation' => 'Academic interpretation', 'oral_tradition' => 'Oral tradition',
                        'community_tradition' => 'Community tradition', 'contemporary_observation' => 'Contemporary observation']],
                    'temporal_scope' => ['label' => 'Time', 'type' => 'select', 'required' => true, 'default' => 'unknown', 'options' => [
                        'current' => 'Current', 'historical' => 'Historical', 'both' => 'Both', 'unknown' => 'Unknown']],
                    'evidence_level' => ['label' => 'Evidence level', 'type' => 'select', 'required' => true, 'default' => 'needs_corroboration',
                        'options' => self::EVIDENCE_LEVEL, 'help' => self::EVIDENCE_LEVEL_HELP],
                    'dispute_id' => ['label' => 'Dispute', 'type' => 'fk', 'fk' => 'claim-disputes', 'help' => 'Required when the level is Disputed.'],
                ] + $sens + [
                    'review_status' => $meta['review_status'],
                    'research_batch_id' => $meta['research_batch_id'],
                ],
                'rules' => [['if' => ['object_table'], 'require' => 'object_id', 'message' => 'Give the related record ID.'],
                            ['when' => ['evidence_level', 'disputed'], 'require' => 'dispute_id', 'message' => 'A disputed claim needs its dispute record.']],
            ],
            'claim-disputes' => [
                'table' => 'claim_disputes', 'label' => 'Dispute', 'plural' => 'Disputes', 'icon' => '&#9878;',
                'title_field' => 'topic', 'no_slug' => true, 'no_knowledge' => true, 'no_dashboard' => true,
                'list' => ['topic', 'nature', 'status'], 'filter' => 'status',
                'fields' => [
                    'topic' => ['label' => 'Topic', 'type' => 'text', 'required' => true, 'help' => 'e.g. "Number of Tiv intermediate areas"'],
                    'nature' => ['label' => 'Kind', 'type' => 'select', 'required' => true, 'options' => [
                        'classification' => 'Classification', 'name' => 'Name', 'date' => 'Date', 'location' => 'Location', 'boundary' => 'Boundary',
                        'homeland' => 'Homeland', 'indigeneity' => 'Indigeneity', 'population' => 'Population', 'origin' => 'Origin', 'other' => 'Other']],
                    'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'default' => 'open', 'options' => [
                        'open' => 'Open', 'resolved' => 'Resolved', 'unresolvable' => 'Unresolvable']],
                    'resolution_note' => ['label' => 'How it was resolved (or why it cannot be)', 'type' => 'textarea', 'rows' => 4],
                    'research_batch_id' => $meta['research_batch_id'],
                ],
                'rules' => [['when' => ['status', 'resolved'], 'require' => 'resolution_note', 'message' => 'Explain how the dispute was resolved.']],
            ],
            'oral-histories' => [
                'table' => 'oral_histories', 'label' => 'Oral history', 'plural' => 'Oral histories', 'icon' => '&#128483;',
                'title_field' => 'title', 'no_dashboard' => true,
                'list' => ['title', 'tradition_type', 'nature', 'sensitivity', 'review_status'], 'filter' => 'tradition_type',
                'fields' => [
                    'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                    'tradition_type' => ['label' => 'Kind of tradition', 'type' => 'select', 'required' => true, 'options' => [
                        'origin' => 'Origin', 'migration' => 'Migration', 'founding' => 'Founding', 'ancestral' => 'Ancestral', 'creation' => 'Creation',
                        'conflict' => 'Conflict', 'settlement' => 'Settlement', 'other' => 'Other']],
                    'nature' => ['label' => 'Nature', 'type' => 'select', 'required' => true, 'default' => 'oral_tradition', 'options' => [
                        'oral_tradition' => 'Oral tradition', 'community_tradition' => 'Community tradition', 'recorded_historical_account' => 'Recorded historical account'],
                        'help' => 'Always shown as a tradition, never as proven fact.'],
                    'community_id' => ['label' => 'Community', 'type' => 'fk', 'fk' => 'communities'],
                    'ethnic_group_id' => ['label' => 'Ethnic group', 'type' => 'fk', 'fk' => 'ethnic-groups'],
                    'admin_unit_id' => $unitsFk,
                    'field_record_id' => ['label' => 'Field recording', 'type' => 'fk', 'fk' => 'field-records'],
                    'summary' => ['label' => 'Summary', 'type' => 'textarea', 'rows' => 3],
                    'transcript' => ['label' => 'Transcript', 'type' => 'textarea', 'rows' => 8],
                    'translation' => ['label' => 'Translation', 'type' => 'textarea', 'rows' => 8],
                ] + $level + [
                    'sensitivity' => ['default' => 'permission_required', 'help' => 'Oral histories need the community\'s permission before they can be public.'] + $sens['sensitivity'],
                    'review_status' => $meta['review_status'],
                    'research_batch_id' => $meta['research_batch_id'],
                ],
            ],
            'contributors' => [
                'table' => 'contributors', 'label' => 'Contributor', 'plural' => 'Contributors & informants', 'icon' => '&#129489;',
                'title_field' => 'display_name', 'no_slug' => true, 'no_knowledge' => true, 'no_dashboard' => true,
                'list' => ['display_name', 'role', 'community_id'], 'filter' => 'role',
                'fields' => [
                    'display_name' => ['label' => 'Name as it may be shown', 'type' => 'text', 'required' => true, 'help' => 'Use a pseudonym if the person asked for one.'],
                    'role' => ['label' => 'Role', 'type' => 'select', 'required' => true, 'options' => [
                        'researcher' => 'Researcher', 'interviewee' => 'Interviewee', 'community_elder' => 'Community elder',
                        'traditional_ruler' => 'Traditional ruler', 'institution' => 'Institution', 'other' => 'Other']],
                    'community_id' => ['label' => 'Community', 'type' => 'fk', 'fk' => 'communities'],
                    'contact_private' => ['label' => 'Contact (private)', 'type' => 'text', 'help' => 'Never shown publicly.'],
                    'consent_reference' => ['label' => 'Consent reference', 'type' => 'text', 'help' => 'Where the signed or recorded consent is kept.'],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rows' => 3],
                ],
            ],
            'field-records' => [
                'table' => 'field_records', 'label' => 'Field record', 'plural' => 'Field records', 'icon' => '&#127897;',
                'title_field' => 'recorded_on', 'no_slug' => true, 'no_knowledge' => true, 'no_dashboard' => true,
                'set_user' => 'researcher_id',
                'list' => ['recorded_on', 'contributor_id', 'admin_unit_id', 'consent_status', 'permission_status'],
                'fields' => [
                    'recorded_on' => ['label' => 'Recorded on', 'type' => 'date', 'required' => true],
                    'contributor_id' => ['label' => 'Speaker / informant', 'type' => 'fk', 'fk' => 'contributors'],
                    'admin_unit_id' => $unitsFk,
                    'community_id' => ['label' => 'Community', 'type' => 'fk', 'fk' => 'communities'],
                    'place_id' => ['label' => 'Place', 'type' => 'fk', 'fk' => 'places'],
                    'consent_status' => ['label' => 'Consent', 'type' => 'select', 'required' => true, 'default' => 'none_recorded', 'options' => [
                        'written_consent' => 'Written consent', 'recorded_verbal_consent' => 'Recorded verbal consent',
                        'institutional_consent' => 'Institutional consent', 'none_recorded' => 'None recorded']],
                    'permission_status' => ['label' => 'Permission to publish', 'type' => 'select', 'required' => true, 'default' => 'pending', 'options' => [
                        'granted' => 'Granted', 'pending' => 'Pending', 'refused' => 'Refused', 'not_required' => 'Not required', 'unknown' => 'Unknown']],
                    'transcript' => ['label' => 'Transcript', 'type' => 'textarea', 'rows' => 8],
                    'translation' => ['label' => 'Translation', 'type' => 'textarea', 'rows' => 8],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rows' => 3],
                ] + $level + [
                    'sensitivity' => ['default' => 'permission_required'] + $sens['sensitivity'],
                ],
            ],
            'media' => [
                'table' => 'media_assets', 'label' => 'Media item', 'plural' => 'Photos, documents & maps', 'icon' => '&#128247;',
                'title_field' => 'title', 'no_slug' => true, 'no_knowledge' => true, 'no_dashboard' => true,
                'panels' => ['media_links'],
                'list' => ['title', 'media_type', 'permission_status', 'review_status'], 'filter' => 'media_type',
                'fields' => [
                    'media_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'help' => 'Audio and video are a later stage.', 'options' => [
                        'photo' => 'Photograph', 'document' => 'Document', 'map' => 'Map', 'manuscript' => 'Manuscript']],
                    'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                    'description' => ['label' => 'Description', 'type' => 'textarea', 'rows' => 3],
                    'file_path' => ['label' => 'File path', 'type' => 'text', 'help' => 'Path under uploads/, if the file is held by the archive.'],
                    'external_url' => ['label' => 'External URL', 'type' => 'text'],
                    'date_created' => ['label' => 'Date made (as stated)', 'type' => 'text', 'maxlength' => 40],
                    'date_precision' => ['label' => 'Date precision', 'type' => 'select', 'options' => self::PRECISION, 'required' => true, 'default' => 'unknown'],
                    'place_id' => ['label' => 'Place', 'type' => 'fk', 'fk' => 'places'],
                    'admin_unit_id' => $unitsFk,
                    'creator' => ['label' => 'Creator (photographer, author)', 'type' => 'text'],
                    'contributor_id' => ['label' => 'Contributed by', 'type' => 'fk', 'fk' => 'contributors'],
                    'copyright_holder' => ['label' => 'Copyright holder', 'type' => 'text'],
                    'licence' => ['label' => 'Licence', 'type' => 'text', 'maxlength' => 100, 'help' => 'e.g. CC BY 4.0, "permission granted", "public domain"'],
                    'permission_status' => ['label' => 'Permission', 'type' => 'select', 'required' => true, 'default' => 'pending', 'options' => [
                        'granted' => 'Granted', 'pending' => 'Pending', 'refused' => 'Refused', 'not_required' => 'Not required', 'unknown' => 'Unknown']],
                    'cultural_restriction' => ['label' => 'Cultural restrictions', 'type' => 'textarea', 'rows' => 2, 'help' => 'e.g. not to be shown to non-initiates.'],
                    'source_id' => ['label' => 'Source', 'type' => 'fk', 'fk' => 'sources'],
                ] + $sens + ['review_status' => $meta['review_status']],
                'publish_requires' => ['permission_status' => ['granted', 'not_required'], 'message' => 'Media can be published only with permission granted (or not required).'],
            ],
            'research-progress' => [
                'table' => 'research_progress', 'label' => 'State research progress', 'plural' => 'Research progress (states)', 'icon' => '&#128200;',
                'title_field' => 'admin_unit_id', 'no_slug' => true, 'no_knowledge' => true, 'no_dashboard' => true,
                'list' => ['admin_unit_id', 'status', 'lgas_total', 'lgas_researched', 'lgas_verified', 'sources_count'], 'filter' => 'status',
                'fields' => [
                    'admin_unit_id' => ['label' => 'State / FCT', 'required' => true] + $unitsFk,
                    'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'default' => 'not_started', 'options' => [
                        'not_started' => 'Not started', 'in_progress' => 'In progress', 'partially_reviewed' => 'Partially reviewed',
                        'qc_in_progress' => 'Quality control', 'complete' => 'Complete', 'needs_update' => 'Needs update'],
                        'help' => 'Complete only when every LGA has been systematically reviewed and has passed quality control.'],
                    'lgas_total' => ['label' => 'LGAs (total)', 'type' => 'number', 'required' => true, 'default' => '0'],
                    'lgas_researched' => ['label' => 'LGAs researched', 'type' => 'number', 'required' => true, 'default' => '0'],
                    'lgas_verified' => ['label' => 'LGAs verified', 'type' => 'number', 'required' => true, 'default' => '0'],
                    'lgas_needs_review' => ['label' => 'LGAs needing review', 'type' => 'number', 'required' => true, 'default' => '0'],
                    'sources_count' => ['label' => 'Sources', 'type' => 'number', 'required' => true, 'default' => '0'],
                    'last_reviewed_at' => ['label' => 'Last reviewed', 'type' => 'date'],
                    'reviewer' => ['label' => 'Reviewer', 'type' => 'text'],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rows' => 3],
                ],
                // The brief: never claim a state complete before every LGA has been systematically reviewed.
                'rules' => [['when' => ['status', 'complete'], 'equal' => ['lgas_verified', 'lgas_total'],
                             'message' => 'A state is complete only when every LGA is verified (LGAs verified must equal LGAs total).']],
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        $e = self::entities()[$key] ?? null;
        if ($e) { $e['key'] = $key; $e['title_field'] = $e['title_field'] ?? 'name'; }
        return $e;
    }

    /** Registry key for a table name, or null. */
    public static function keyForTable(string $table): ?string
    {
        foreach (self::entities() as $k => $e) {
            if ($e['table'] === $table) return $k;
        }
        return null;
    }
}
