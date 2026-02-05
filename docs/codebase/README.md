# Archon Knowledge Base - RAG Optimized

**Status:** ✅ **Production Ready**
**Generated:** 2026-01-30
**Source:** Tree-Sitter Analysis + Deterministic Optimization

---

## Quick Start

Your knowledge base is ready for RAG ingestion:

```python
import json

# Load the optimized KB
kb = {}
with open('knowledge_base_optimized.jsonl', 'r') as f:
    for line in f:
        rec = json.loads(line)
        kb[rec['id']] = rec

print(f"Loaded {len(kb)} records")  # 3,260 records

# Load routing indexes for fast filtering
import json
with open('index/by_type.json') as f:
    by_type = json.load(f)

# Example: Get all WordPress action hooks
action_ids = by_type['action']  # 493 hooks
actions = [kb[aid] for aid in action_ids]
```

---

## What's Inside

### Core Files

| File | Records | Description |
|------|---------|-------------|
| **knowledge_base_optimized.jsonl** | 3,260 | Main knowledge base (JSONL format) |
| **index/by_type.json** | 7 types | Route by entity type (class, function, action, etc.) |
| **index/by_area.json** | 6 areas | Route by code area (theme, app, plugin, etc.) |
| **index/by_scope.json** | 1 scope | Route by scope (core only, tests excluded) |
| **index/by_path_prefix.json** | 64 prefixes | Route by file path prefix |
| **index/stats.json** | - | Index statistics and metadata |

### Documentation

| File | Purpose |
|------|---------|
| **README.md** | This file - overview and quick start |
| **USAGE_GUIDE.md** | Detailed usage examples and query patterns |
| **optimization_report.md** | Full optimization report with before/after |

### Scripts

| Script | Purpose |
|--------|---------|
| **../optimize_kb.py** | Optimization script (re-run to regenerate) |
| **../validate_kb.py** | Validation queries (run to test KB) |

---

## Key Improvements

### Before Optimization
❌ 3,939 records (including 679 tests/stubs)
❌ 0% tagged - no categorization
❌ Weak grounding - line numbers only
❌ No summaries - raw snippets only
❌ No routing - slow, mixed retrieval

### After Optimization
✅ 3,260 core records (tests excluded)
✅ 100% tagged - full categorization
✅ Enhanced grounding - line ranges + excerpts
✅ Deterministic summaries - semantic descriptions
✅ 5 routing indexes - fast targeted retrieval

---

## Record Structure

Every record now has this schema:

```json
{
  "id": "action:admin_init",
  "type": "action",
  "name": "admin_init",
  "summary": "Registers action hook 'admin_init'",
  "tags": [
    "area:theme",
    "lang:php",
    "scope:core",
    "tier:tier1_internal"
  ],
  "sources": [
    {
      "file": "app/...",
      "line": 42,
      "start_line": 40,
      "end_line": 45,
      "excerpt": "...",
      "context": "ClassName::methodName"
    }
  ],
  "count": 10,
  "signature": "...",
  "docblock": "...",
  "snippets": ["..."],
  "context_id": "class:someclass"
}
```

---

## Statistics

### By Type
- **function**: 1,996 (functions and methods)
- **action**: 493 (WordPress action hooks)
- **filter**: 467 (WordPress filter hooks)
- **class**: 222 (class definitions)
- **shortcode**: 66 (WordPress shortcodes)
- **cpt**: 13 (custom post types)
- **interface**: 3 (PHP interfaces)

### By Area
- **theme**: 2,427 (WordPress theme code)
- **app**: 451 (application/business logic)
- **migration**: 236 (database migrations)
- **mu-plugin**: 130 (must-use plugins)
- **bootstrap**: 19 (bootstrap/initialization)
- **script**: 2 (utility scripts)

### Quality Metrics
- ✅ **3,260** records with tags (100%)
- ✅ **3,260** records with summaries (100%)
- ✅ **3,260** records with enhanced grounding (100%)
- ✅ **679** test/stub records excluded
- ✅ **64** path prefixes indexed

---

## Tag System

### Available Tags

**Scope:**
- `scope:core` - Production code (3,260 records)
- `scope:test` - Test files (excluded)
- `scope:stub` - Stub/mock files (excluded)
- `scope:dealer` - Dealer-specific content (excluded)

**Area:**
- `area:theme` - WordPress themes
- `area:plugin` - WordPress plugins
- `area:mu-plugin` - Must-use plugins
- `area:app` - Application code
- `area:migration` - Database migrations
- `area:bootstrap` - Bootstrap files
- `area:infrastructure` - Infrastructure code
- `area:script` - Utility scripts

**Language:**
- `lang:php` - PHP files
- `lang:js` - JavaScript
- `lang:ts` - TypeScript
- `lang:css` - CSS
- `lang:scss` - SCSS

**Tier:**
- `tier:tier1_internal` - Our code (2,915 records)
- `tier:tier2_dependency` - Third-party plugins (345 records)
- `tier:tier3_vendor` - Vendor libraries (0 records)

---

## Common Queries

### Find all WordPress hooks
```python
actions = by_type['action']    # 493 action hooks
filters = by_type['filter']    # 467 filter hooks
```

### Find all shortcodes
```python
shortcodes = by_type['shortcode']  # 66 shortcodes
for sc_id in shortcodes:
    sc = kb[sc_id]
    print(f"[{sc['name']}] - {sc['summary']}")
```

### Find app-level classes only
```python
app_ids = by_area['app']
app_classes = [kb[aid] for aid in app_ids if kb[aid]['type'] == 'class']
# 81 app classes
```

### Filter by tier (internal code only)
```python
tier1 = [rec for rec in kb.values() if 'tier:tier1_internal' in rec['tags']]
# 2,915 internal records
```

### Most frequently used hooks
```python
hooks = [kb[hid] for hid in by_type['action'] + by_type['filter']]
top_hooks = sorted(hooks, key=lambda x: x['count'], reverse=True)[:10]
# Top 10: wp_before_body (129x), init (80x), wp_enqueue_scripts (80x), ...
```

---

## Validation

Run the validation script to test queries:

```bash
cd /Users/nathanhart/di-websites-platform/_tsa-output
python3 validate_kb.py
```

This runs 7 real-world queries demonstrating:
- Hook filtering
- Custom post type discovery
- App class discovery
- Shortcode listing
- Frequency analysis
- Tier filtering
- Grounding validation

---

## RAG Integration Recommendations

### 1. Two-Stage Retrieval
**Stage 1:** Use indexes to narrow domain (fast)
**Stage 2:** Semantic search within candidates (accurate)

### 2. Hybrid Search
Combine:
- **Semantic search** on `summary` field (embed these)
- **Exact filters** on `tags` (scope, area, tier)
- **Type routing** via `by_type` index

### 3. Grounding for Trust
Use `sources[].start_line`, `end_line`, and `excerpt` to:
- Show proof in responses
- Link to exact code locations
- Verify snippet accuracy

### 4. Pre-filtering Strategies
Before semantic search, filter by:
- **Type** if user asks about "hooks" or "shortcodes"
- **Area** if user asks about "WordPress" vs "app"
- **Tier** if user wants "our code" vs "dependencies"

---

## Maintenance

### Re-running Optimization

If you update the codebase and re-extract with Tree-Sitter:

```bash
cd /Users/nathanhart/di-websites-platform/_tsa-output
python3 optimize_kb.py
```

This regenerates:
- `knowledge_base_optimized.jsonl`
- All routing indexes
- Optimization report

### Customizing Tag Rules

Edit `optimize_kb.py` function `generate_tags()` to adjust:
- Scope detection (what counts as test/core/dealer)
- Area classification (how to categorize paths)
- Tier assignment (internal vs dependency vs vendor)

All changes are deterministic and repeatable (no LLM calls).

---

## ChatGPT Analysis Validation

The ChatGPT analysis you received was **100% accurate**. This optimization addresses all issues:

✅ **Tags are now populated** (was: 100% empty)
✅ **Grounding is enhanced** (was: only line numbers)
✅ **Tests/stubs filtered** (was: 679 contaminating records)
✅ **Summaries added** (was: raw snippets only)
✅ **Routing indexes created** (was: none)

---

## Next Steps

1. ✅ **Test retrieval** - Run `validate_kb.py` to see it in action
2. ✅ **Review samples** - Check `optimization_report.md` for examples
3. ✅ **Study query patterns** - Read `USAGE_GUIDE.md` for detailed examples
4. ⏭️ **Ingest into RAG** - Use `knowledge_base_optimized.jsonl`
5. ⏭️ **Embed summaries** - These are designed for semantic search
6. ⏭️ **Leverage indexes** - Use for fast routing before embedding search

---

## Questions?

This KB was generated deterministically from your Tree-Sitter analysis. All transformations are:
- **Reproducible** - Re-run anytime with same results
- **Deterministic** - No LLM calls, pure rule-based logic
- **Customizable** - Edit `optimize_kb.py` to adjust rules
- **Fast** - Processes 3,939 records in seconds

The knowledge base is now **fully optimized for RAG ingestion** and ready for production use.
