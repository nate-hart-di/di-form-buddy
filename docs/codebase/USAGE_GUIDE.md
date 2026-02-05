# Optimized Knowledge Base Usage Guide

## Overview

Your knowledge base has been transformed from a raw Tree-Sitter extraction into a **RAG-optimized, fully categorized, and indexed knowledge base** ready for ingestion into your RAG system.

## What Changed

### Before Optimization
- **3,939 records** (including 679 test/stub records)
- **0% tagged** - No categorization
- **Weak grounding** - Only line numbers, no ranges or excerpts
- **No summaries** - Raw snippets only
- **No routing** - Mixed domains, slow retrieval
- **Contaminated** - Tests and stubs mixed with production code

### After Optimization
- **3,260 core records** (679 test/stub records excluded)
- **100% tagged** - Every record categorized by scope, area, language, tier
- **Enhanced grounding** - Line ranges + bounded excerpts for verification
- **Deterministic summaries** - Semantic descriptions for every entity
- **5 routing indexes** - Fast, targeted retrieval by type, area, scope, prefix
- **Clean** - Production code only, tests excluded

## Files Generated

```
archon_docs/
├── knowledge_base_optimized.jsonl     # Main KB (3,260 records)
├── optimization_report.md             # Detailed optimization report
├── USAGE_GUIDE.md                     # This file
└── index/
    ├── by_type.json                   # Route by entity type
    ├── by_area.json                   # Route by code area
    ├── by_scope.json                  # Route by scope
    ├── by_path_prefix.json            # Route by file path
    └── stats.json                     # Index statistics
```

## Record Schema

Every record now has this enhanced structure:

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
      "file": "app/dealer-inspire/wp-content/themes/...",
      "line": 42,
      "start_line": 40,
      "end_line": 45,
      "excerpt": "... code excerpt ...",
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

## Tag System

### Scope Tags
- `scope:core` - Core platform code (3,260 records)
- `scope:dealer` - Dealer-specific content (excluded by default)
- `scope:test` - Test files (excluded)
- `scope:stub` - Stub/mock files (excluded)

### Area Tags
- `area:theme` - WordPress theme files (2,427 records)
- `area:plugin` - WordPress plugins
- `area:mu-plugin` - Must-use plugins (130 records)
- `area:app` - Application code (451 records)
- `area:migration` - Database migrations (236 records)
- `area:bootstrap` - Bootstrap files (19 records)
- `area:infrastructure` - Infrastructure code
- `area:script` - Scripts (2 records)

### Language Tags
- `lang:php` - PHP files
- `lang:js` - JavaScript files
- `lang:ts` - TypeScript files
- `lang:css` - CSS files
- `lang:scss` - SCSS files

### Tier Tags
- `tier:tier1_internal` - Internally developed code
- `tier:tier2_dependency` - Third-party dependencies/plugins
- `tier:tier3_vendor` - Vendor libraries

## Query Examples

### Example 1: Find All WordPress Action Hooks

**Using type index:**
```python
import json

# Load the type index
with open('index/by_type.json') as f:
    by_type = json.load(f)

action_ids = by_type['action']  # 493 action hooks
print(f"Found {len(action_ids)} action hooks")

# Load specific records
with open('knowledge_base_optimized.jsonl') as f:
    for line in f:
        rec = json.loads(line)
        if rec['id'] in action_ids[:5]:  # First 5 actions
            print(f"- {rec['name']}: {rec['summary']}")
```

### Example 2: Find All App-Level Code (Not WordPress)

**Using area index:**
```python
import json

with open('index/by_area.json') as f:
    by_area = json.load(f)

app_ids = by_area['app']  # 451 app records
print(f"Found {len(app_ids)} app-level entities")

# Get all classes in app/
with open('knowledge_base_optimized.jsonl') as f:
    kb = {json.loads(line)['id']: json.loads(line) for line in f}

app_classes = [kb[eid] for eid in app_ids if kb[eid]['type'] == 'class']
print(f"\nApp classes ({len(app_classes)}):")
for cls in app_classes[:10]:
    print(f"  - {cls['name']} ({cls['sources'][0]['file']})")
```

### Example 3: Find All Shortcodes in Themes

**Using combined filters:**
```python
import json

with open('index/by_type.json') as f:
    by_type = json.load(f)

with open('knowledge_base_optimized.jsonl') as f:
    kb = {json.loads(line)['id']: json.loads(line) for line in f}

shortcode_ids = by_type['shortcode']  # 66 shortcodes
theme_shortcodes = [
    kb[sid] for sid in shortcode_ids
    if 'area:theme' in kb[sid]['tags']
]

print(f"Found {len(theme_shortcodes)} theme shortcodes:")
for sc in theme_shortcodes:
    print(f"  [{sc['name']}] - {sc['sources'][0]['file']}")
```

### Example 4: Find All Infrastructure Classes

**Using path prefix index:**
```python
import json

with open('index/by_path_prefix.json') as f:
    by_prefix = json.load(f)

infra_ids = by_prefix.get('app/src/Infrastructure', [])  # 85 records

with open('knowledge_base_optimized.jsonl') as f:
    kb = {json.loads(line)['id']: json.loads(line) for line in f}

infra_classes = [kb[eid] for eid in infra_ids if kb[eid]['type'] == 'class']
print(f"Infrastructure classes ({len(infra_classes)}):")
for cls in infra_classes:
    print(f"  - {cls['name']}")
```

### Example 5: Full-Text Search with Filtering

**RAG query pattern:**
```python
import json

def search_kb(query_text: str, filters: dict = None):
    """
    Search KB with optional filters.

    filters = {
        'type': ['class', 'function'],
        'area': ['app'],
        'scope': ['core'],
        'tier': ['tier1_internal']
    }
    """
    with open('knowledge_base_optimized.jsonl') as f:
        for line in f:
            rec = json.loads(line)

            # Apply filters
            if filters:
                if 'type' in filters and rec['type'] not in filters['type']:
                    continue
                if 'area' in filters:
                    if not any(f'area:{a}' in rec['tags'] for a in filters['area']):
                        continue
                if 'scope' in filters:
                    if not any(f'scope:{s}' in rec['tags'] for s in filters['scope']):
                        continue
                if 'tier' in filters:
                    if not any(f'tier:{t}' in rec['tags'] for t in filters['tier']):
                        continue

            # Check if query matches
            searchable = f"{rec['name']} {rec['summary']} {rec.get('docblock', '')}"
            if query_text.lower() in searchable.lower():
                yield rec

# Example: Find all internal app classes related to "inventory"
results = list(search_kb(
    'inventory',
    filters={'type': ['class'], 'area': ['app'], 'tier': ['tier1_internal']}
))

print(f"Found {len(results)} results:")
for r in results:
    print(f"  - {r['name']}: {r['summary']}")
```

## RAG System Integration

### Recommended Ingestion Strategy

1. **Load indexes into memory** for fast routing
2. **Pre-filter by area/scope** before embedding search
3. **Use summaries for semantic search** (they're concise and meaningful)
4. **Use grounding for verification** (line ranges + excerpts)
5. **Leverage tags for hybrid search** (combine semantic + exact filters)

### Example: Two-Stage Retrieval

```python
# Stage 1: Use indexes to narrow domain
if 'WordPress hook' in user_query:
    candidate_ids = by_type['action'] + by_type['filter']
elif 'shortcode' in user_query:
    candidate_ids = by_type['shortcode']
elif 'application' in user_query or 'backend' in user_query:
    candidate_ids = by_area['app']
else:
    candidate_ids = None  # Search all

# Stage 2: Semantic search within candidates
if candidate_ids:
    kb_subset = {eid: kb[eid] for eid in candidate_ids}
else:
    kb_subset = kb

# Embed and search summaries
embeddings = embed([rec['summary'] for rec in kb_subset.values()])
results = semantic_search(user_query_embedding, embeddings, top_k=5)
```

## Statistics

- **Total optimized records:** 3,260
- **Test/stub records excluded:** 679
- **Record types:** 7 (class, function, action, filter, shortcode, cpt, interface)
- **Code areas:** 6 (theme, app, migration, mu-plugin, bootstrap, script)
- **Path prefixes indexed:** 64
- **Records with tags:** 100%
- **Records with summaries:** 100%
- **Records with enhanced grounding:** 100%

## Distribution by Type

| Type       | Count | Description                           |
|------------|-------|---------------------------------------|
| function   | 1,996 | Functions and methods                 |
| action     |   493 | WordPress action hooks                |
| filter     |   467 | WordPress filter hooks                |
| class      |   222 | Class definitions                     |
| shortcode  |    66 | WordPress shortcodes                  |
| cpt        |    13 | Custom post types                     |
| interface  |     3 | PHP interfaces                        |

## Distribution by Area

| Area         | Count | Description                        |
|--------------|-------|------------------------------------|
| theme        | 2,427 | WordPress theme code               |
| app          |   451 | Application/business logic         |
| migration    |   236 | Database migrations                |
| mu-plugin    |   130 | Must-use plugins                   |
| bootstrap    |    19 | Bootstrap/initialization           |
| script       |     2 | Utility scripts                    |

## Next Steps

1. **Test retrieval** with sample queries from your use cases
2. **Measure performance** - indexes should dramatically speed up routing
3. **Tune filters** - adjust tag generation rules if needed
4. **Embed summaries** - these are designed for semantic search
5. **Ingest into RAG** - use the optimized JSONL as your source of truth

## Maintenance

To re-optimize after updating the codebase:

```bash
# Re-run Tree-Sitter extraction (see walkthrough.md)
# Then re-run optimization
cd /Users/nathanhart/di-websites-platform/_tsa-output
python3 optimize_kb.py
```

## Feedback

If you find records that should be:
- **Excluded** - Adjust filtering rules in `optimize_kb.py:should_exclude_record()`
- **Re-tagged** - Adjust tag generation in `optimize_kb.py:generate_tags()`
- **Re-summarized** - Adjust summary logic in `optimize_kb.py:generate_summary()`

The optimization is **deterministic and repeatable** - no LLM calls, just rule-based transformations.
