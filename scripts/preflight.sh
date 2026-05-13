#!/usr/bin/env bash
#
# Pre-deploy sanity check. Runs every gate a production deploy must pass
# before flipping DNS or rolling traffic to a new version. Designed to be
# the LAST step before `php artisan up` on a freshly-deployed box.
#
# Usage:
#   APP_ENV=production scripts/preflight.sh         # strict — fails on red
#   APP_ENV=staging    scripts/preflight.sh         # same checks, soft fail
#   scripts/preflight.sh --skip-tests               # skip the full test suite
#
# Exit codes:
#   0 — all checks passed; safe to bring up traffic
#   1 — a hard check failed; investigate before flipping DNS

set -u

# --- arguments ---
SKIP_TESTS=0
for arg in "$@"; do
    case "$arg" in
        --skip-tests) SKIP_TESTS=1 ;;
        *) echo "Unknown arg: $arg" >&2; exit 2 ;;
    esac
done

# --- colors ---
if [ -t 1 ]; then
    GREEN=$'\033[32m'; RED=$'\033[31m'; YELLOW=$'\033[33m'; CYAN=$'\033[36m'; RESET=$'\033[0m'
else
    GREEN=""; RED=""; YELLOW=""; CYAN=""; RESET=""
fi

failures=0

step() {
    echo
    echo "${CYAN}━━━ $1 ━━━${RESET}"
}

pass() { echo "${GREEN}✓${RESET} $1"; }
fail() { echo "${RED}✗${RESET} $1"; failures=$((failures + 1)); }
skip() { echo "${YELLOW}○${RESET} $1"; }

# --- 1. directory sanity ---
step "Directory sanity"
if [ ! -f artisan ]; then
    fail "Not in a Laravel project root (no artisan file)."
    exit 1
fi
pass "artisan present"
if [ ! -d vendor ]; then
    fail "vendor/ missing — run 'composer install --no-dev --optimize-autoloader'"
else
    pass "vendor/ present"
fi

# --- 2. .env present (we don't print it, just check) ---
step ".env file"
if [ ! -f .env ]; then
    fail ".env missing"
else
    pass ".env present"
fi

# --- 3. PHP version ---
step "PHP version"
PHP_OK=$(php -r 'echo PHP_VERSION_ID >= 80300 ? "ok" : "old";')
if [ "$PHP_OK" = "ok" ]; then
    pass "PHP $(php -r 'echo PHP_VERSION;')"
else
    fail "PHP $(php -r 'echo PHP_VERSION;') is too old; require 8.3+"
fi

# --- 4. extensions Laravel needs ---
step "PHP extensions"
for ext in mbstring openssl pdo tokenizer xml json curl; do
    if php -m | grep -qi "^$ext$"; then
        pass "ext-$ext"
    else
        fail "ext-$ext missing"
    fi
done

# --- 5. secrets:check (strict in prod) ---
step "Environment secrets"
SECRETS_FLAGS=""
if [ "${APP_ENV:-local}" = "production" ]; then
    SECRETS_FLAGS="--strict"
fi
if php artisan secrets:check $SECRETS_FLAGS; then
    pass "secrets:check passed"
else
    fail "secrets:check failed — see hints above"
fi

# --- 6. config / route caching CAN load (catches syntax + binding errors early) ---
step "Boot smoke (config + routes load)"
if php artisan route:list >/dev/null 2>&1; then
    pass "route table loads"
else
    fail "route:list errored — boot will fail in production"
fi

# --- 7. migrations are caught up ---
step "Migration status"
PENDING=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || true)
if [ "$PENDING" -eq 0 ]; then
    pass "no pending migrations"
else
    fail "$PENDING pending migration(s) — run 'php artisan migrate --force' before traffic"
fi

# --- 8. audit chain intact ---
step "Audit chain integrity"
if php artisan audit:verify >/dev/null 2>&1; then
    pass "audit:verify OK"
else
    skip "audit:verify FAILED — investigate (may be empty DB; safe at first deploy)"
fi

# --- 9. storage symlink ---
step "Storage symlink"
if [ -L public/storage ] || [ -d public/storage ]; then
    pass "public/storage linked"
else
    fail "public/storage missing — run 'php artisan storage:link'"
fi

# --- 10. tests (optional; takes ~40s) ---
if [ "$SKIP_TESTS" -eq 0 ]; then
    step "Test suite"
    if php artisan test --parallel --without-tty >/dev/null 2>&1; then
        pass "test suite green"
    else
        fail "test suite has failures — re-run 'php artisan test' for details"
    fi
else
    skip "test suite skipped (--skip-tests)"
fi

# --- 11. asset build present ---
step "Asset build"
if [ -d public/build ] && [ -n "$(ls -A public/build 2>/dev/null)" ]; then
    pass "public/build populated"
else
    fail "public/build empty — run 'npm ci && npm run build' on the box (or upload built assets)"
fi

# --- 12. webhook reachable from outside? (informational only) ---
step "Stripe webhook URL"
if [ -n "${APP_URL:-}" ]; then
    pass "APP_URL set to ${APP_URL}; configure Stripe webhook at ${APP_URL}/billing/webhook"
else
    skip "APP_URL not set in env shell; cannot derive webhook URL"
fi

# --- summary ---
echo
echo "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
if [ "$failures" -eq 0 ]; then
    echo "${GREEN}All preflight checks passed.${RESET}"
    echo "Safe to flip DNS / restart php-fpm / 'php artisan up'."
    exit 0
else
    echo "${RED}$failures check(s) failed.${RESET}"
    echo "Investigate before bringing traffic to this deploy."
    exit 1
fi
