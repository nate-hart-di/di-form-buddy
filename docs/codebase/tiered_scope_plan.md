# Refined Scope Logic - Tiered Plugins

## Classification Rules (in `verify_scope.py`)

1.  **Tier 1 (Core Platform)**:
    - Path matches `app/dealer-inspire/wp-content/plugins/(vessel|di-|dealerinspire-|maven-|inventory-)`.
    - **Action**: FULL_ANALYSIS (Include in `allowlist`).

2.  **Tier 2 (Critical Third-Party)**:
    - Path matches `(advanced-custom-fields|wpml|wordpress-seo|yoast)`.
    - **Action**: SURFACE_ONLY (Include, but tag for specific extraction of hooks/filters usage, not internal classes).
    - _Implementation_: For now, add to `allowlist` but maybe add a separate `integration_allowlist` or just strict filtering later.

3.  **Tier 3 (Builders / Low-Signal)**:
    - Path matches `(elementor|visual-composer|beaver-builder|ultimate_vc)`.
    - **Action**: EXCLUDE (Add to `denylist`).
    - _Exception_: If a file explicitly extends these (e.g., `class MyWidget extends ElementorWidget`), it might be caught by the "Importance Heuristic" in Pass 3.

4.  **Tier 4 (Low Impact)**:
    - Plugin Score < 5.0 AND References == 0 AND Not Tier 1.
    - **Action**: EXCLUDE (Add to `denylist`).

## Script Updates

- Load `plugin_ranking.json`.
- Apply these regex rules to `path`.
- Override metrics/score if it's Tier 1 (Always Include).
