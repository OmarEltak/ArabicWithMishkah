<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * HTTP client for eastlaws.com that handles login, session cookies, and the
 * AJAX endpoints we reverse-engineered from the public site:
 *
 *   GET  /Identity/Account/Login            – antiforgery cookie + token
 *   POST /Identity/Account/Login            – credential login
 *   GET  /Tash/getCntryList?ListType=1      – JSON: list of country IDs
 *   POST /Tash/GetResulsByTash              – multipart: search results HTML
 *   GET  /Tash/GetFullText?RecID&RecType    – HTML: full document body
 *
 * The session cookie (.AspNetCore.Identity.Application) is cached for ~12h so
 * we avoid re-logging in on every request and don't trip the site's auth flow
 * unnecessarily.
 *
 * Throttling is enforced between every outbound call (default 3s) to be
 * respectful of the upstream service's capacity.
 */
class EastlawsClient
{
    private const SESSION_CACHE_KEY = 'eastlaws.session.cookies';

    private const SESSION_TTL_SECONDS = 12 * 3600;

    private ?string $cookieJar = null;

    private float $lastRequestAt = 0.0;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $username,
        private readonly ?string $password,
        private readonly int $requestDelayMs = 3000,
    ) {
        if (! function_exists('curl_init')) {
            throw new RuntimeException('php-curl extension is required for the eastlaws client.');
        }
    }

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: rtrim((string) config('services.eastlaws.base_url', 'https://www.eastlaws.com'), '/'),
            username: (string) (config('services.eastlaws.username') ?? '') ?: null,
            password: (string) (config('services.eastlaws.password') ?? '') ?: null,
            requestDelayMs: (int) config('services.eastlaws.request_delay_ms', 3000),
        );
    }

    public function isConfigured(): bool
    {
        return $this->username !== null && $this->password !== null
            && $this->username !== '' && $this->password !== '';
    }

    /**
     * Ensure we have a valid authenticated session. Returns true on success.
     */
    public function ensureLoggedIn(bool $force = false): bool
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Eastlaws credentials are not configured.');
        }

        if (! $force) {
            $cached = Cache::get(self::SESSION_CACHE_KEY);
            if (is_string($cached) && file_exists($cached) && filesize($cached) > 0) {
                $this->cookieJar = $cached;

                return true;
            }
        }

        $this->cookieJar = tempnam(sys_get_temp_dir(), 'el_');
        if (! is_string($this->cookieJar)) {
            throw new RuntimeException('Failed to create cookie jar.');
        }

        // 1) GET login page to obtain antiforgery cookie + token.
        $loginHtml = $this->raw('GET', $this->baseUrl.'/Identity/Account/Login');
        $token = $this->extractCsrfToken($loginHtml);
        if ($token === null) {
            throw new RuntimeException('Failed to extract antiforgery token from login page.');
        }

        // 2) POST credentials.
        $payload = http_build_query([
            'Input.UserName' => $this->username,
            'Input.Password' => $this->password,
            'Input.RememberMe' => 'true',
            '__RequestVerificationToken' => $token,
        ]);
        $this->raw('POST', $this->baseUrl.'/Identity/Account/Login', $payload, [
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: '.$this->baseUrl.'/Identity/Account/Login',
            'Origin: '.$this->baseUrl,
        ]);

        if (! $this->cookieJarHas('.AspNetCore.Identity.Application')) {
            throw new RuntimeException('Eastlaws login failed: no auth cookie was set. Verify credentials.');
        }

        Cache::put(self::SESSION_CACHE_KEY, $this->cookieJar, self::SESSION_TTL_SECONDS);

        return true;
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    public function listCountries(int $listType = 1): array
    {
        $this->ensureLoggedIn();
        $json = $this->raw('GET', $this->baseUrl.'/Tash/getCntryList?ListType='.$listType, null, [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
        ]);
        $rows = json_decode($json, true);
        if (! is_array($rows)) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['id'])) {
                $out[] = [
                    'id' => (int) $row['id'],
                    'name' => (string) ($row['name'] ?? ''),
                ];
            }
        }

        return $out;
    }

    /**
     * Run a legislation search. Returns the HTML fragment of result rows from
     * /Tash/GetResulsByTash.
     *
     * @param  array<string, string|int>  $overrides  Extra fields to override defaults.
     */
    public function searchLegislation(string $allText, int $countryId = 1, int $pageNo = 1, array $overrides = []): string
    {
        $this->ensureLoggedIn();

        // Antiforgery token must come from a fresh search page load.
        $searchHtml = $this->raw('GET', $this->baseUrl.'/legislation-search');
        $token = $this->extractCsrfToken($searchHtml);
        if ($token === null) {
            throw new RuntimeException('Failed to extract antiforgery token for search.');
        }

        $fields = array_merge([
            '__RequestVerificationToken' => $token,
            'OpenType' => '1',
            'PageNo' => (string) $pageNo,
            'OrderBy' => 'orderBy',
            'OrderIndex' => '1',
            'OrderDir' => 'Descending',
            'TypeIDs' => '',
            'CntryIDsFilter' => '',
            'TypeIDsFilter' => '',
            'YearsFilter' => '',
            'cntryID' => (string) $countryId,
            'AllText' => $allText,
            'txt_Tit' => '',
            'txt_MadaText' => '',
            'txt_Fehres' => '',
            'txt_Smart' => '',
            'txt_Tawkee3' => '',
            'search_by' => 'on',
            'Ta3deelID' => '-1',
            'OldRelationID' => '-1',
            'IFSary' => '-1',
            'CrimeType' => '-1',
            'NoFrom' => '',
            'NoTo' => '',
        ], array_map('strval', $overrides));

        $body = $this->buildMultipart($fields);

        return $this->raw('POST', $this->baseUrl.'/Tash/GetResulsByTash', $body['payload'], [
            'Content-Type: multipart/form-data; boundary='.$body['boundary'],
            'Referer: '.$this->baseUrl.'/legislation-search',
            'X-Requested-With: XMLHttpRequest',
            'Origin: '.$this->baseUrl,
        ]);
    }

    /**
     * Parse a results-fragment HTML and return discovered documents.
     *
     * @return array<int, array{id:int, recType:int, slug:string}>
     */
    public static function parseResults(string $html): array
    {
        $found = [];
        // Non-greedy capture (`+?`) and stricter char class — exclude any
        // chars that would let the match cross over into the next result
        // row's HTML. Slugs are URL paths so they only contain
        // [a-z0-9\-/_.] in practice.
        if (preg_match_all(
            "/openTashFullText\(\s*(\d+)\s*,\s*(\d+)\s*,\s*\d+\s*,\s*&#x27;([A-Za-z0-9_\-\/.]+?)&#x27;\s*\)/u",
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $m) {
                $key = $m[1].'|'.$m[2];
                if (isset($found[$key])) {
                    continue;
                }
                $found[$key] = [
                    'id' => (int) $m[1],
                    'recType' => (int) $m[2],
                    'slug' => htmlspecialchars_decode((string) $m[3]),
                ];
            }
        }

        return array_values($found);
    }

    /**
     * Fetch the full HTML body of a document.
     */
    public function fetchFullText(int $recId, int $recType = 1): string
    {
        $this->ensureLoggedIn();

        return $this->raw(
            'GET',
            $this->baseUrl.'/Tash/GetFullText?RecID='.$recId.'&RecType='.$recType.'&colorText=',
            null,
            [
                'X-Requested-With: XMLHttpRequest',
                'Referer: '.$this->baseUrl.'/legislation-full-text/x?type='.$recType.'&id='.$recId.'&sub=0',
            ]
        );
    }

    /**
     * Convert eastlaws full-text HTML into clean plain text.
     */
    public static function htmlToCleanText(string $html): string
    {
        // Drop scripts/styles, then strip tags and decode entities.
        $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/iu', ' ', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/iu', ' ', $html) ?? $html;
        // Normalise paragraph/break boundaries.
        $html = preg_replace('/<br\s*\/?>/iu', "\n", $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|h[1-6])\s*>/iu', "\n", $html) ?? $html;

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }

    /** Try to extract a clean title from full-text HTML. */
    public static function extractTitleFromFullText(string $html): ?string
    {
        if (preg_match('/<h[12][^>]*>(.*?)<\/h[12]>/su', $html, $m)) {
            $t = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($t !== '') {
                return mb_substr($t, 0, 240);
            }
        }
        $clean = self::htmlToCleanText($html);
        if ($clean === '') {
            return null;
        }
        $first = mb_substr($clean, 0, 240);

        return trim($first) ?: null;
    }

    private function extractCsrfToken(string $html): ?string
    {
        if (preg_match('/name="__RequestVerificationToken"[^>]*value="([^"]+)"/u', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    private function cookieJarHas(string $name): bool
    {
        if (! is_string($this->cookieJar) || ! file_exists($this->cookieJar)) {
            return false;
        }
        $contents = (string) file_get_contents($this->cookieJar);

        return str_contains($contents, $name);
    }

    /**
     * @param  array<string, string>  $fields
     * @return array{payload:string, boundary:string}
     */
    private function buildMultipart(array $fields): array
    {
        $boundary = '----lawyerformBoundary'.bin2hex(random_bytes(8));
        $body = '';
        foreach ($fields as $name => $value) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
            $body .= $value."\r\n";
        }
        $body .= "--{$boundary}--\r\n";

        return ['payload' => $body, 'boundary' => $boundary];
    }

    /**
     * @param  array<int, string>  $extraHeaders
     */
    private function raw(string $method, string $url, ?string $body = null, array $extraHeaders = []): string
    {
        $this->throttle();

        $ch = curl_init();
        $headers = array_merge([
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/json;q=0.9,*/*;q=0.8',
            'Accept-Language: ar,en;q=0.9',
            'Connection: keep-alive',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_COOKIEFILE => $this->cookieJar ?? '',
            CURLOPT_COOKIEJAR => $this->cookieJar ?? '',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST' && $body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || ! is_string($response)) {
            Log::channel('ai')->warning('Eastlaws curl error', ['url' => $url, 'errno' => $errno, 'error' => $error]);
            throw new RuntimeException("Eastlaws request failed: {$error}");
        }
        if ($status >= 500) {
            Log::channel('ai')->warning('Eastlaws server error', ['url' => $url, 'status' => $status]);
            throw new RuntimeException("Eastlaws returned HTTP {$status}");
        }

        return $response;
    }

    private function throttle(): void
    {
        $delaySeconds = $this->requestDelayMs / 1000;
        $elapsed = microtime(true) - $this->lastRequestAt;
        if ($this->lastRequestAt > 0 && $elapsed < $delaySeconds) {
            $sleepUs = (int) round(($delaySeconds - $elapsed) * 1_000_000);
            if ($sleepUs > 0) {
                usleep($sleepUs);
            }
        }
        $this->lastRequestAt = microtime(true);
    }
}
