<?php
/**
 * StableId — permanent, human-readable record IDs (NIGERIA_STEP4 §2, STEP5 §8), e.g.
 * NG-STATE-BENUE, NG-LGA-BENUE-GBOKO, ETH-TIV, COMM-BENUE-GBOKO-MBAYION, CLAIM-000123.
 * Generated once from the name when a record is created and never regenerated
 * (the same rules database/research/backfill_002.php used for existing records).
 */
class StableId
{
    /** Prefix per table for name-based IDs. */
    private const PREFIX = [
        'places' => 'PLACE', 'ethnic_groups' => 'ETH', 'languages' => 'LANG', 'polities' => 'POL',
        'cultural_records' => 'CULT', 'historical_periods' => 'PERIOD', 'timeline_events' => 'EVT', 'historical_figures' => 'PER',
    ];
    /** Prefix per table for number-based IDs (records without a stable name). */
    private const NUMBERED = ['claims' => 'CLAIM', 'oral_histories' => 'ORAL', 'media_assets' => 'MEDIA', 'field_records' => 'FIELD'];
    private const NAME_COLUMN = ['timeline_events' => 'title', 'historical_figures' => 'english_name'];

    public static function norm(string $s): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        return trim(preg_replace('/[^A-Z0-9]+/', '-', strtoupper($s)), '-');
    }

    /** Sets stable_id on a newly created record if it has none. Returns the ID, or null for tables without one. */
    public static function assign(PDO $db, string $table, int $id): ?string
    {
        $cols = $db->query("SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = " . $db->quote($table))
                   ->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('stable_id', $cols, true)) return null;
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || !empty($row['stable_id'])) return $row['stable_id'] ?? null;

        $sid = self::generate($db, $table, $row);
        if ($sid === null) return null;
        $taken = $db->prepare("SELECT 1 FROM {$table} WHERE stable_id = ? AND id != ?");
        $taken->execute([$sid, $id]);
        if ($taken->fetchColumn()) $sid = self::cut($sid, 80 - strlen((string) $id) - 1) . '-' . $id;
        $keepTs = in_array('updated_at', $cols, true) ? ', updated_at = updated_at' : '';
        $db->prepare("UPDATE {$table} SET stable_id = ?{$keepTs} WHERE id = ? AND stable_id IS NULL")->execute([$sid, $id]);
        return $sid;
    }

    public static function generate(PDO $db, string $table, array $row): ?string
    {
        if (isset(self::NUMBERED[$table])) return self::NUMBERED[$table] . '-' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT);
        if ($table === 'admin_units') return self::adminUnit($db, $row);
        if ($table === 'communities') {
            $unit = self::unit($db, (int) $row['admin_unit_id']);
            $chain = [];
            for ($u = $unit, $n = 0; $u && $n < 4 && !in_array($u['unit_type'], ['country', 'geopolitical_zone'], true); $u = self::unit($db, (int) $u['parent_id']), $n++) {
                array_unshift($chain, self::unitKey($u));
            }
            return self::cut('COMM-' . implode('-', array_merge($chain, [self::norm($row['name'])])));
        }
        if (isset(self::PREFIX[$table])) {
            return self::cut(self::PREFIX[$table] . '-' . self::norm((string) $row[self::NAME_COLUMN[$table] ?? 'name']));
        }
        return null;
    }

    private static function adminUnit(PDO $db, array $u): ?string
    {
        $parent = $u['parent_id'] ? self::unit($db, (int) $u['parent_id']) : null;
        $sid = match ($u['unit_type']) {
            'country' => 'NG',
            'region' => 'NG-REGION-' . self::norm(preg_replace('/ Region$/', '', $u['name'])),
            'geopolitical_zone' => 'NG-ZONE-' . self::norm($u['name']),
            'state' => 'NG-STATE-' . self::unitKey($u),
            'federal_capital_territory' => 'NG-FCT',
            'lga', 'other' => $parent && in_array($parent['unit_type'], ['state', 'federal_capital_territory'], true)
                ? 'NG-LGA-' . self::unitKey($parent) . '-' . self::norm($u['name']) : null,
            // Wards: the INEC code is preferred when recorded (NG-WARD-<STATE>-<LGA>-<code>).
            'ward' => $parent && $parent['unit_type'] === 'lga' && ($state = self::unit($db, (int) $parent['parent_id']))
                ? 'NG-WARD-' . self::unitKey($state) . '-' . self::norm($parent['name']) . '-' . self::norm($u['official_code'] ?: $u['name']) : null,
            default => null,
        };
        return $sid ? self::cut($sid) : null;
    }

    /** Short key of a state/FCT/LGA used inside IDs: "BENUE", "FCT", "GBOKO". */
    private static function unitKey(array $u): string
    {
        return $u['unit_type'] === 'federal_capital_territory' ? 'FCT' : self::norm(preg_replace('/ State$/', '', $u['name']));
    }

    private static function unit(PDO $db, int $id): ?array
    {
        if (!$id) return null;
        $stmt = $db->prepare('SELECT id, unit_type, parent_id, name, official_code FROM admin_units WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private static function cut(string $id, int $max = 80): string
    {
        return strlen($id) <= $max ? $id : rtrim(substr($id, 0, strrpos(substr($id, 0, $max), '-') ?: $max), '-');
    }
}
