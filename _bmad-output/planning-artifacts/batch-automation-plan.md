# Batch Automation Plan: Full Platform Validation

**Date:** 2026-02-05
**Author:** Mary (Business Analyst) + Nate
**Status:** DRAFT - Pending Approval

---

## Executive Summary

**Full coverage validation** of DI Form Buddy across **8,244 live production sites** on 47 pods. Test **every site**, **every active form**, with comprehensive error tracking mapped to lead team documentation.

**Phases:**
1. **Phase A (Health Checks):** Validate all 8,244 sites, all forms — no submissions
2. **Phase B (Submission Testing):** Submit test leads to all passing forms
3. **Phase C (Error Analysis):** Categorize errors per lead team troubleshooting docs

**Estimated scope:**
- ~8,244 sites × ~5 forms avg = ~41,000 form health checks
- ~30,000+ test submissions (assuming 75% pass rate)

---

## Architecture Overview

```
Local Machine                         Pod (via SSH)
┌─────────────────────────┐           ┌─────────────────────────┐
│  batch-runner.sh        │           │  di-form-buddy.php      │
│  ├── dealers.json       │──SSH───▶  │  ├── --health-check     │
│  ├── results/           │           │  ├── --generate-configs │
│  ├── configs/           │◀──JSON──  │  └── --submit           │
│  └── errors/            │           └─────────────────────────┘
│      └── categorized/   │
└─────────────────────────┘

Error Categorization (maps to Lead Team docs):
┌─────────────────────────────────────────────────────────┐
│  errors/categorized/                                     │
│  ├── validation/         # Required fields, format      │
│  ├── spam/               # Spam detection triggers      │
│  ├── recaptcha/          # reCAPTCHA failures           │
│  ├── infrastructure/     # WP/GFAPI/form missing        │
│  └── unknown/            # Uncategorized                │
└─────────────────────────────────────────────────────────┘
```

---

## Error Categories (Lead Team Alignment)

Based on `docs/confluence/OEM/Troubleshooting+Forms.md` and `000Lead Form Delivery.md`:

| Category | Error Pattern | Lead Team Doc Reference |
|----------|---------------|-------------------------|
| `validation` | Required field missing, invalid email/phone | Form Notifications setup |
| `spam` | "numeric characters in name field", spam_reason | Troubleshooting Forms |
| `recaptcha` | "error with the reCAPTCHA" | Troubleshooting Forms |
| `infrastructure` | WP not found, GFAPI unavailable, form missing | N/A (our tool) |
| `oem_config` | Shift ID missing, Lead Manifold failure | OEM-specific docs |

**The 16 Core Lead Forms** (from Lead Form Delivery doc):
1. Get E-Price
2. Order Parts
3. Contact Us
4. Contact Commercial
5. Contact Parts
6. Contact Service
7. Schedule Test Drive
8. Vehicle Finder Service
9. Schedule Service
10. Check Availability
11. Employment
12. Schedule A Bodyshop Appt
13. Value Trade In
14. Lock Lease
15. Lock Payment
16. Ask a Question

---

## Phase A: Full Health Check Campaign

### Goal
Health check **every live site** and **every form** without submitting leads.

### Implementation: `scripts/health-check-campaign.sh`

**Inputs:**
- `dealers.json` from devtools cache (~8,244 live sites)
- Optional: `--pods=N,M,...` to process specific pods first
- Optional: `--resume` to continue from last checkpoint

**Outputs:**
- `results/health-checks/YYYY-MM-DD/all-results.jsonl` - every result
- `results/health-checks/YYYY-MM-DD/by-pod/pod{N}.jsonl` - per-pod files
- `results/health-checks/YYYY-MM-DD/summary.json` - aggregate stats
- `results/health-checks/YYYY-MM-DD/failures.jsonl` - failed sites only

**Algorithm:**
```bash
1. Load dealers.json
2. Filter to LIVE SITES ONLY:
   - has_production_site == 1
   - pod > 0
3. Group by pod (for SSH connection reuse)
4. For each pod (1-47):
   a. Establish SSH connection (with multiplexing)
   b. For each site on pod:
      - Execute: php di-form-buddy.php --site={domain} --health-check --all --output=json
      - Append to all-results.jsonl and pod{N}.jsonl
      - If failure, append to failures.jsonl
      - Checkpoint progress every 100 sites
   c. Log pod completion
5. Generate summary.json
6. Categorize failures by error type
```

### CLI Interface

```bash
# Full run - all pods, all sites
./scripts/health-check-campaign.sh

# Process specific pods first (testing)
./scripts/health-check-campaign.sh --pods=1,2,3

# Resume from checkpoint after interruption
./scripts/health-check-campaign.sh --resume

# Parallel mode: run N pods simultaneously
./scripts/health-check-campaign.sh --parallel=4

# Dry run
./scripts/health-check-campaign.sh --dry-run
```

### Progress Output

```
[Health Check] 2026-02-05 14:30:00
[Health Check] Source: dealers.json (8,244 live sites on 47 pods)
[Health Check] Mode: FULL COVERAGE
[Health Check] ═══════════════════════════════════════════════════════
[Health Check] Pod 1: 144 sites
[Health Check]   [1/144] cochran.com — PASS (5/5 forms)
[Health Check]   [2/144] dealer2.com — PASS (3/3 forms)
[Health Check]   [3/144] dealer3.com — PARTIAL (2/4 forms)
[Health Check]   ...
[Health Check]   [144/144] dealerN.com — PASS (6/6 forms)
[Health Check] Pod 1 complete: 142/144 healthy (98.6%)
[Health Check] ─────────────────────────────────────────────────────
[Health Check] Pod 2: 134 sites
...
[Health Check] ═══════════════════════════════════════════════════════
[Health Check] CAMPAIGN COMPLETE
[Health Check]   Total sites: 8,244
[Health Check]   Fully healthy: 7,832 (95.0%)
[Health Check]   Partial: 312 (3.8%)
[Health Check]   Failed: 100 (1.2%)
[Health Check]   Total forms checked: 41,220
[Health Check]   Forms passing: 39,159 (95.0%)
[Health Check] Results: results/health-checks/2026-02-05/
```

### Estimated Runtime

| Sites/Pod | Time/Site | Pod Time | Total (Sequential) | Total (4 Parallel) |
|-----------|-----------|----------|--------------------|--------------------|
| ~175 avg | ~3 sec | ~9 min | ~7 hours | ~2 hours |

---

## Phase B: Full Submission Campaign

### Goal
Submit test leads to **all forms that passed health checks**.

### Prerequisites
- Phase A complete
- `DI_FORM_BUDDY_SECRET` set
- `DI_FORM_BUDDY_EMAIL` set (all notifications reroute here)

### Implementation: `scripts/submission-campaign.sh`

**Inputs:**
- Health check results from Phase A
- Generated configs (created on-the-fly per site)

**Outputs:**
- `results/submissions/YYYY-MM-DD/all-results.jsonl`
- `results/submissions/YYYY-MM-DD/by-pod/pod{N}.jsonl`
- `results/submissions/YYYY-MM-DD/summary.json`
- `results/submissions/YYYY-MM-DD/errors.jsonl` - submission failures
- `configs/{domain}/` - generated configs per site

**Algorithm:**
```bash
1. Load health check results (filter to status=pass or partial)
2. For each site with passing forms:
   a. SSH to pod
   b. Generate configs: --generate-configs --output=json
   c. For each form that passed health check:
      - Submit: --site={domain} --form={id} --config=/tmp/{config} --secret=$SECRET --output=json
      - Record result
      - 1-second delay between submissions
   d. Download configs to local configs/{domain}/
3. Categorize submission errors
4. Generate summary
```

### CLI Interface

```bash
# Full run from health check results
./scripts/submission-campaign.sh --from-health-check=results/health-checks/2026-02-05/

# Test specific pods only
./scripts/submission-campaign.sh --from-health-check=... --pods=1,2,3

# Limit forms per site (for initial testing)
./scripts/submission-campaign.sh --from-health-check=... --max-forms=3

# Filter by form name (core lead forms only)
./scripts/submission-campaign.sh --from-health-check=... --form-pattern="E-Price|Contact|Test Drive"

# Resume from checkpoint
./scripts/submission-campaign.sh --resume

# Require confirmation for large runs
./scripts/submission-campaign.sh --from-health-check=... --yes
```

### Safety Features

1. **Notification rerouting:** All emails → `DI_FORM_BUDDY_EMAIL`
2. **Test data:** Entries use "Test User", test email, identifiable data
3. **Rate limiting:** 1-second delay between submissions
4. **Confirmation:** Require `--yes` for >100 submissions
5. **Checkpointing:** Resume after interruption
6. **Dry run:** `--dry-run` to preview without executing

---

## Phase C: Error Analysis & Reporting

### Goal
Categorize all errors against lead team documentation for actionable insights.

### Implementation: `scripts/analyze-errors.sh`

**Error Categorization Logic:**
```bash
# Validation errors
if message contains "required" or "invalid" → validation/

# Spam detection
if message contains "spam" or "spam_reason" → spam/

# reCAPTCHA (shouldn't happen with our bypass, but track)
if message contains "recaptcha" or "captcha" → recaptcha/

# Infrastructure
if error in [bootstrap_failed, gfapi_unavailable, form_not_found] → infrastructure/

# OEM-specific (post-submission)
if message contains "Shift" or "Lead Manifold" → oem_config/

# Unknown
else → unknown/
```

### Reports Generated

1. **errors/summary.md** - Executive summary for lead team
2. **errors/by-category/** - Detailed errors grouped by type
3. **errors/by-oem/** - Errors grouped by OEM (from dealer data)
4. **errors/by-form-type/** - Errors grouped by form name
5. **errors/actionable.json** - Structured data for automated fixes

### Sample Error Summary

```markdown
# Error Analysis Report - 2026-02-05

## Executive Summary
- Sites tested: 8,244
- Forms tested: 41,220
- Submissions attempted: 39,159
- Successful: 37,201 (95.0%)
- Failed: 1,958 (5.0%)

## Errors by Category

### Validation (1,247 errors - 63.7%)
| Field | Count | Common Message |
|-------|-------|----------------|
| Email | 523 | "Please enter a valid email address" |
| Phone | 312 | "Phone number format invalid" |
| Name | 198 | Required field |
| Zip | 214 | "Please enter a valid zip code" |

### Spam Detection (412 errors - 21.0%)
| Reason | Count |
|--------|-------|
| numeric characters in name | 312 |
| blacklisted domain | 67 |
| honeypot triggered | 33 |

### Infrastructure (156 errors - 8.0%)
| Error | Count |
|-------|-------|
| form_not_found | 89 |
| gfapi_unavailable | 45 |
| bootstrap_failed | 22 |

### OEM Config (98 errors - 5.0%)
| Issue | Count |
|-------|-------|
| Shift ID not generated | 67 |
| Lead Manifold failure | 31 |

### Unknown (45 errors - 2.3%)
[Detailed in errors/unknown/]
```

---

## File Structure

```
di-form-buddy/
├── di-form-buddy.php              # Core tool (exists)
├── scripts/
│   ├── health-check-campaign.sh   # Phase A
│   ├── submission-campaign.sh     # Phase B
│   ├── analyze-errors.sh          # Phase C
│   └── lib/
│       ├── dealers.sh             # Dealer data helpers
│       ├── ssh.sh                 # SSH multiplexing helpers
│       ├── progress.sh            # Progress/checkpoint helpers
│       └── categorize.sh          # Error categorization
├── configs/
│   └── {domain}/                  # Per-site configs
│       ├── 01-get-e-price.json
│       ├── 03-contact-us.json
│       └── ...
├── results/
│   ├── health-checks/
│   │   └── YYYY-MM-DD/
│   │       ├── all-results.jsonl
│   │       ├── by-pod/
│   │       ├── summary.json
│   │       └── failures.jsonl
│   └── submissions/
│       └── YYYY-MM-DD/
│           ├── all-results.jsonl
│           ├── by-pod/
│           ├── summary.json
│           └── errors.jsonl
└── errors/
    └── YYYY-MM-DD/
        ├── summary.md
        ├── by-category/
        ├── by-oem/
        ├── by-form-type/
        └── actionable.json
```

---

## Implementation Stories

### Story 3.1: Health Check Campaign Script (Full Coverage)

**As an operator, I want** to health-check every live site on the platform,
**So that** I have complete visibility into form readiness.

**Acceptance Criteria:**
1. Reads dealers.json from devtools cache path
2. **CRITICAL:** Filters to live sites only (`has_production_site == 1` AND `pod > 0`)
3. Processes ALL sites (8,244) — no sampling
4. Groups by pod for SSH connection efficiency
5. Uses SSH multiplexing to reduce connection overhead
6. Checkpoints progress every 100 sites for resume capability
7. Outputs per-pod JSONL + aggregate summary
8. Supports `--pods=N,M,...` for phased rollout
9. Supports `--parallel=N` for concurrent pod processing
10. Supports `--resume` to continue from checkpoint
11. Handles SSH failures gracefully (retry 3x, then skip with log)

**Estimated effort:** ~200 lines bash

### Story 3.2: Submission Campaign Script (Full Coverage)

**As an operator, I want** to submit test leads to all passing forms,
**So that** I can validate end-to-end lead delivery across the entire platform.

**Acceptance Criteria:**
1. Consumes health check results (only tests passing/partial sites)
2. Generates configs on-the-fly via `--generate-configs`
3. Submits to ALL forms that passed health check on each site
4. Downloads generated configs to local `configs/{domain}/`
5. 1-second delay between submissions (configurable)
6. Checkpoints progress for resume capability
7. Requires `--yes` flag for >100 submissions
8. Supports `--form-pattern` to filter by form name
9. Supports `--max-forms` per site (for testing)
10. Categorizes errors as they occur

**Estimated effort:** ~250 lines bash

### Story 3.3: Error Analysis & Categorization

**As an operator, I want** errors categorized per lead team documentation,
**So that** I can prioritize fixes and share actionable reports.

**Acceptance Criteria:**
1. Categorizes errors into: validation, spam, recaptcha, infrastructure, oem_config, unknown
2. Groups by OEM (using dealer data `oem_name` field)
3. Groups by form type (from form name in results)
4. Generates markdown summary report
5. Generates structured JSON for automated processing
6. Identifies top 10 most common errors
7. Maps errors to lead team troubleshooting doc references

**Estimated effort:** ~150 lines bash/jq

### Story 3.4: SSH Multiplexing & Performance

**As an operator, I want** efficient SSH handling,
**So that** the full campaign completes in reasonable time.

**Acceptance Criteria:**
1. Uses SSH ControlMaster for connection multiplexing
2. One persistent connection per pod
3. Configurable connection timeout and retry logic
4. Cleans up connections on script exit
5. Supports parallel pod processing (`--parallel=N`)

**Estimated effort:** ~50 lines bash

---

## Execution Sequence

```
Week 1: Phase A Implementation & Initial Run
├── Day 1-2: Story 3.1 + Story 3.4 (health check + SSH optimization)
├── Day 3: Test on pods 1-3 (full coverage ~400 sites)
├── Day 4-5: Run on all 47 pods (~8,244 sites)
└── Deliverable: Complete health check results

Week 2: Phase B Implementation & Run
├── Day 1-2: Story 3.2 (submission campaign)
├── Day 3: Test submissions on pods 1-3
├── Day 4-5: Full submission run on passing sites
└── Deliverable: Complete submission results + configs

Week 3: Phase C Analysis & Refinement
├── Day 1-2: Story 3.3 (error analysis)
├── Day 3: Generate reports, share with lead team
├── Day 4-5: Fix common validation issues in test data generator
└── Deliverable: Error report, improved tool
```

---

## Risk Mitigation

| Risk | Mitigation |
|------|------------|
| Testing non-live sites | Filter requires `has_production_site == 1` AND `pod > 0` |
| Campaign interrupted | Checkpoint every 100 sites, `--resume` flag |
| SSH rate limiting | SSH multiplexing, 47 persistent connections |
| Pod unavailable | Retry 3x, skip with log, continue to next |
| Too many submissions | Require `--yes` for >100, 1-sec delay |
| Dealer notification | All notifications rerouted to test inbox |
| Error flood | Categorize and aggregate, report top N |
| Long runtime | Parallel pod processing, estimate ~2 hours |

---

## Success Criteria

**Phase A Complete When:**
- Health checks run on all 8,244 live sites
- All ~41,000 forms checked
- Failures documented and categorized
- Summary shows health rate (target: >90%)

**Phase B Complete When:**
- Submissions attempted on all passing forms (~30,000+)
- Configs generated and stored for all tested sites
- Success rate >95% on healthy forms
- All errors categorized

**Phase C Complete When:**
- Error report generated with lead team categories
- Top errors identified with counts
- Report shared with lead team
- Actionable JSON available for automated fixes

---

## Approvals

- [x] Full coverage (all sites, all forms) — **Confirmed by Nate**
- [x] Live sites only filtering — **Implemented**
- [ ] Error categories align with lead team docs — **Review needed**
- [ ] Parallel processing approach — **Pending**
- [ ] Config storage strategy (commit vs ephemeral) — **Pending**

---

**Prepared by:** Mary (Business Analyst)
**Date:** 2026-02-05
**Ready for:** Implementation of Story 3.1
