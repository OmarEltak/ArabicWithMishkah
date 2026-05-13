<?php

declare(strict_types=1);

namespace App\Services\Audit;

/**
 * Canonical signing helper for the audit-log HMAC chain.
 *
 * Pure, dependency-free, easy to unit-test. Two responsibilities:
 *   1. Build a canonical, deterministic payload for any audit row
 *      (so re-signing later produces the same hash byte-for-byte)
 *   2. HMAC-SHA256 sign that payload with APP_AUDIT_KEY
 *
 * The signing key MUST be a high-entropy random value held only by the
 * server. Rotating it forfeits the verifiability of all rows signed
 * under the previous key — there is no graceful migration. If you need
 * to rotate, archive the old log under the old key, then start a new
 * chain from chain_index = 0 under the new key.
 */
final class HashChain
{
    /**
     * Build the canonical payload string for a row.
     *
     * Order matters and must NEVER change — it's part of the cryptographic
     * commitment. Any change here breaks verification of every existing row.
     *
     * @param  array<string, mixed>  $row
     */
    public static function canonicalPayload(array $row): string
    {
        // Stable order. Empty values normalized to ''. JSON for the
        // metadata blob with sorted keys for determinism.
        $metadata = $row['metadata'] ?? null;
        $metaCanonical = $metadata === null
            ? ''
            : self::canonicalJson($metadata);

        return implode("\x1F", [
            'v1',                                              // schema version of the canonical form
            (string) ($row['chain_index'] ?? ''),
            (string) ($row['prev_hash'] ?? str_repeat('0', 64)),
            (string) ($row['user_id'] ?? ''),
            (string) ($row['subject_type'] ?? ''),
            (string) ($row['subject_id'] ?? ''),
            (string) ($row['action'] ?? ''),
            (string) ($row['summary'] ?? ''),
            $metaCanonical,
            (string) ($row['ip'] ?? ''),
            (string) ($row['user_agent'] ?? ''),
            (string) ($row['created_at'] ?? ''),
        ]);
    }

    /**
     * HMAC-SHA256 sign a canonical payload. Returns 64-char lowercase hex.
     */
    public static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, self::key());
    }

    /**
     * Convenience: combine canonical-form + sign in one call.
     *
     * @param  array<string, mixed>  $row
     */
    public static function signRow(array $row): string
    {
        return self::sign(self::canonicalPayload($row));
    }

    /**
     * Look up the signing key. Reads APP_AUDIT_KEY (env or config), falls
     * back to APP_KEY for development. In production, APP_AUDIT_KEY MUST
     * be set to a separate, dedicated random secret.
     */
    public static function key(): string
    {
        $key = (string) config('app.audit_key', '');

        if ($key === '') {
            // In production, refuse to silently fall back to APP_KEY —
            // sharing the audit-chain secret with the general encryption
            // layer weakens the integrity guarantee (a leak of APP_KEY
            // would also let an attacker forge audit rows).
            if (app()->environment('production')) {
                throw new \RuntimeException(
                    'APP_AUDIT_KEY is not set. Generate one with `php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"` and set it in your production environment. Refusing to fall back to APP_KEY.'
                );
            }

            // Non-production fallback only.
            $key = (string) config('app.key', '');
            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }
        }

        if ($key === '') {
            throw new \RuntimeException('No signing key available. Set APP_AUDIT_KEY in .env.');
        }

        return $key;
    }

    /**
     * Deterministic JSON encoding: keys recursively sorted, no extra
     * whitespace, Unicode preserved (not escaped).
     */
    public static function canonicalJson(mixed $value): string
    {
        $value = self::deepKsort($value);

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Recursively ksort assoc arrays. Numeric arrays preserve order.
     */
    private static function deepKsort(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        $isAssoc = array_keys($value) !== range(0, count($value) - 1);
        if ($isAssoc) {
            ksort($value);
        }
        foreach ($value as $k => $v) {
            $value[$k] = self::deepKsort($v);
        }

        return $value;
    }
}
