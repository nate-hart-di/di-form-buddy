# Knowledge Base Optimization Report

**Generated:** Fri Jan 30 12:54:52 MST 2026

## Summary

- **Input records:** 3939
- **Output records:** 3260
- **Excluded (tests/stubs):** 679
- **Records with tags added:** 3260
- **Records with summaries added:** 3260
- **Records with enhanced grounding:** 3260

## Optimizations Applied

### 1. Deterministic Tags
- **scope:** `core`, `dealer`, `test`, `stub`
- **area:** `theme`, `plugin`, `mu-plugin`, `app`, `migration`, `infrastructure`, `bootstrap`, `script`
- **lang:** `php`, `js`, `ts`, `css`, `scss`
- **tier:** `tier1_internal`, `tier2_dependency`, `tier3_vendor`

### 2. Enhanced Grounding
- Added `start_line` and `end_line` to all sources
- Added `excerpt` field with bounded snippet (max 500 chars)

### 3. Deterministic Summaries
- Generated concise, semantic descriptions for all records
- Format: "Registers action hook 'name'" or "Defines class ClassName"

### 4. Test/Stub Filtering
- Excluded 679 records from test/stub paths

### 5. Routing Indexes
- Generated `index/by_type.json`
- Generated `index/by_area.json`
- Generated `index/by_scope.json`
- Generated `index/by_path_prefix.json`
- Generated `index/stats.json`

## Sample Records

### Sample 1: class - InventoryUpdateBootstrap

```json
{
  "id": "class:inventoryupdatebootstrap",
  "type": "class",
  "name": "InventoryUpdateBootstrap",
  "sources": [
    {
      "file": "app/bootstrap/inventory_update.php",
      "line": 13,
      "context": "",
      "start_line": 11,
      "end_line": 16,
      "excerpt": " * \n * Don't import anything. We haven't run the main bootstrap yet so we have no autoloaders until we do.\n * For that reason, you have to use fully qualified class names in this class.  \n */\n\nclass InventoryUpdateBootstrap\n{\n    private const EXIT_SUCCESS = 0;\n    private const EXIT_FAILURE = 1;\n    private const EXIT_DB_ERROR = 2;\n"
    }
  ],
  "count": 1,
  "tags": [
    "area:bootstrap",
    "lang:php",
    "scope:core",
    "tier:tier1_internal"
  ],
  "docblock": "/**\n * Bootstrap for running containerized inventory updates.\n * \n * Class is self-invoking at the bottom of the file.\n * \n * Don't import anything. We haven't run the main bootstrap yet so we have no autoloaders until we do.\n * For that reason, you have to use fully qualified class names in this class.  \n */",
  "signature": "class InventoryUpdateBootstrap",
  "snippets": [
    " * \n * Don't import anything. We haven't run the main bootstrap yet so we have no autoloaders until we do.\n * For that reason, you have to use fully qualified class names in this class.  \n */\n\nclass InventoryUpdateBootstrap\n{\n    private const EXIT_SUCCESS = 0;\n    private const EXIT_FAILURE = 1;\n    private const EXIT_DB_ERROR = 2;\n"
  ],
  "context_id": "",
  "summary": "Defines class InventoryUpdateBootstrap"
}
```

### Sample 2: function - InventoryUpdateBootstrap::bootstrap

```json
{
  "id": "function:inventoryupdatebootstrap::bootstrap",
  "type": "function",
  "name": "InventoryUpdateBootstrap::bootstrap",
  "sources": [
    {
      "file": "app/bootstrap/inventory_update.php",
      "line": 19,
      "context": "InventoryUpdateBootstrap",
      "start_line": 17,
      "end_line": 22,
      "excerpt": "{\n    private const EXIT_SUCCESS = 0;\n    private const EXIT_FAILURE = 1;\n    private const EXIT_DB_ERROR = 2;\n\n    public function bootstrap(): void\n    {\n        try {\n            $this->main();\n            echo \"Done\" . PHP_EOL;\n            exit(self::EXIT_SUCCESS);"
    }
  ],
  "count": 1,
  "tags": [
    "area:bootstrap",
    "lang:php",
    "scope:core",
    "tier:tier1_internal"
  ],
  "docblock": "",
  "signature": "public function bootstrap(): void",
  "snippets": [
    "{\n    private const EXIT_SUCCESS = 0;\n    private const EXIT_FAILURE = 1;\n    private const EXIT_DB_ERROR = 2;\n\n    public function bootstrap(): void\n    {\n        try {\n            $this->main();\n            echo \"Done\" . PHP_EOL;\n            exit(self::EXIT_SUCCESS);"
  ],
  "context_id": "class:inventoryupdatebootstrap",
  "summary": "Method InventoryUpdateBootstrap::bootstrap"
}
```

### Sample 3: function - InventoryUpdateBootstrap::main

```json
{
  "id": "function:inventoryupdatebootstrap::main",
  "type": "function",
  "name": "InventoryUpdateBootstrap::main",
  "sources": [
    {
      "file": "app/bootstrap/inventory_update.php",
      "line": 36,
      "context": "InventoryUpdateBootstrap",
      "start_line": 34,
      "end_line": 39,
      "excerpt": "        echo $e->getTraceAsString();\n        exit(self::EXIT_FAILURE);\n    }\n}\n\nprivate function main(): void\n{\n    $this->registerShutdownFunction();\n    $this->loadMainBootstrap();\n    $this->closeAllOutputBuffers();\n    $this->registerResqueGroupIdInDB();"
    }
  ],
  "count": 1,
  "tags": [
    "area:bootstrap",
    "lang:php",
    "scope:core",
    "tier:tier1_internal"
  ],
  "docblock": "",
  "signature": "private function main(): void",
  "snippets": [
    "        echo $e->getTraceAsString();\n        exit(self::EXIT_FAILURE);\n    }\n}\n\nprivate function main(): void\n{\n    $this->registerShutdownFunction();\n    $this->loadMainBootstrap();\n    $this->closeAllOutputBuffers();\n    $this->registerResqueGroupIdInDB();"
  ],
  "context_id": "class:inventoryupdatebootstrap",
  "summary": "Method InventoryUpdateBootstrap::main"
}
```

## Next Steps

1. Review the optimized knowledge base: `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/knowledge_base_optimized.jsonl`
2. Check routing indexes in: `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/index/`
3. Validate with sample queries
4. Ingest into your RAG system

## Files Generated

- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/knowledge_base_optimized.jsonl` - Optimized knowledge base (JSONL)
- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/index/by_type.json` - Type routing index
- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/index/by_area.json` - Area routing index
- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/index/by_scope.json` - Scope routing index
- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/index/by_path_prefix.json` - Path prefix routing index
- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/index/stats.json` - Index statistics
- `/Users/nathanhart/di-websites-platform/_tsa-output/archon_docs/optimization_report.md` - This report
