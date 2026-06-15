<?php
/**
 * Team Member Model
 */

require_once BASE_PATH . '/core/Model.php';

class TeamMember extends Model
{
    protected string $table = 'team_members';

    protected array $fillable = [
        'user_id',
        'name',
        'role',
        'category',
        'short_bio',
        'full_bio',
        'image',
        'contributions',
        'socials',
        'sort_order',
        'is_active',
    ];

    /** All active members ordered by sort_order */
    public function getActive(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
            );
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /** All members for admin (active + inactive) */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC"
        );
        return $stmt->fetchAll();
    }

    /** Decode the socials JSON into an array */
    public static function decodeSocials(?string $json): array
    {
        if (empty($json)) return [];
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /** Decode the contributions JSON into an array */
    public static function decodeContributions(?string $json): array
    {
        if (empty($json)) return [];
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Calculate real contribution stats for all active members from the DB.
     * Members without a linked user_id get score = 0.
     * Returns the members array with 'contrib_score', 'contrib_pct', 'contrib_breakdown' added.
     */
    public function withContribStats(array $members): array
    {
        $empty = function(array &$arr): void {
            $arr['contrib_score']      = 0;
            $arr['contrib_pct']        = 0;
            $arr['contrib_breakdown']  = [];
            $arr['contrib_highlights'] = [];
        };

        // Grand total of all content across every table
        // Build grand total — skip any table that doesn't exist
        $allTables = ['tiv_names', 'tiv_proverbs', 'tiv_plants', 'tiv_festivals', 'tiv_foods', 'daily_words', 'learning_videos'];
        $existingTables = [];
        foreach ($allTables as $tbl) {
            try {
                $this->db->query("SELECT 1 FROM `{$tbl}` LIMIT 1");
                $existingTables[] = $tbl;
            } catch (\Exception $e) { /* table missing — skip */ }
        }
        if (empty($existingTables)) {
            foreach ($members as &$m) { $empty($m); }
            return $members;
        }
        $grandTotal = 0;
        foreach ($existingTables as $tbl) {
            try {
                $grandTotal += (int) $this->db->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
            } catch (\Exception $e) {}
        }

        if ($grandTotal === 0) {
            foreach ($members as &$m) { $empty($m); }
            return $members;
        }

        foreach ($members as &$m) {
            $uid = isset($m['user_id']) ? (int)$m['user_id'] : null;

            if (!$uid) {
                $empty($m);
                continue;
            }

            // Query only tables that exist
            $tableMap = [
                'tiv_names'       => 'Names',
                'tiv_proverbs'    => 'Proverbs',
                'tiv_plants'      => 'Plants',
                'tiv_festivals'   => 'Festivals',
                'tiv_foods'       => 'Foods',
                'daily_words'     => 'Words',
                'learning_videos' => 'Videos',
            ];
            $row = [];
            foreach ($tableMap as $tbl => $label) {
                if (!in_array($tbl, $existingTables)) { $row[strtolower($label)] = 0; continue; }
                try {
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE created_by = ?");
                    $stmt->execute([$uid]);
                    $row[strtolower($label)] = (int) $stmt->fetchColumn();
                } catch (\Exception $e) {
                    $row[strtolower($label)] = 0;
                }
            }

            $userTotal = array_sum($row);
            $breakdown = [
                'Names'     => (int)($row['names']     ?? 0),
                'Proverbs'  => (int)($row['proverbs']  ?? 0),
                'Plants'    => (int)($row['plants']     ?? 0),
                'Festivals' => (int)($row['festivals']  ?? 0),
                'Foods'     => (int)($row['foods']      ?? 0),
                'Words'     => (int)($row['words']      ?? 0),
                'Videos'    => (int)($row['videos']     ?? 0),
            ];

            $highlights = [];
            foreach ($breakdown as $label => $count) {
                if ($count > 0) {
                    $highlights[] = "Added {$count} {$label}";
                }
            }

            $m['contrib_score']      = $userTotal;
            $m['contrib_pct']        = (int) round(($userTotal / $grandTotal) * 100);
            $m['contrib_breakdown']  = $breakdown;
            $m['contrib_highlights'] = $highlights;

            // Keep the stored contributions column in sync so the
            // admin form and any fallback always show live numbers.
            if (!empty($highlights)) {
                $encoded = json_encode($highlights);
                if ($encoded !== ($m['contributions'] ?? null)) {
                    try {
                        $upd = $this->db->prepare(
                            "UPDATE {$this->table} SET contributions = ? WHERE id = ?"
                        );
                        $upd->execute([$encoded, $m['id']]);
                        $m['contributions'] = $encoded;
                    } catch (\Exception $e) { /* non-fatal */ }
                }
            }
        }
        unset($m);

        return $members;
    }

    /** All supported social platforms with labels and icons */
    public static function socialPlatforms(): array
    {
        return [
            'linkedin'  => ['label' => 'LinkedIn',   'icon' => '&#76;'],
            'twitter'   => ['label' => 'Twitter / X', 'icon' => '&#120143;'],
            'facebook'  => ['label' => 'Facebook',    'icon' => '&#102;'],
            'instagram' => ['label' => 'Instagram',   'icon' => '&#128247;'],
            'tiktok'    => ['label' => 'TikTok',      'icon' => '&#127925;'],
            'whatsapp'  => ['label' => 'WhatsApp',    'icon' => '&#128172;'],
            'youtube'   => ['label' => 'YouTube',     'icon' => '&#127909;'],
            'github'    => ['label' => 'GitHub',      'icon' => '&#9881;'],
            'telegram'  => ['label' => 'Telegram',    'icon' => '&#9992;'],
            'website'   => ['label' => 'Website',     'icon' => '&#127760;'],
        ];
    }
}
