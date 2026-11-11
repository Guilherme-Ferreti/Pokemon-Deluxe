---
paths:
  - 'tests/**/Browser/**'
---

# Browser

## Use Pest browser testing with visit() for smoke tests
Browser tests use `visit()`, `click()`, `fill()`, `assertNoSmoke()`, `assertNoAccessibilityIssues()`. Place in `tests/{Domain}/Browser/`.

## Run `npm run build:testing` before running browser tests
Browser tests run against the `testing` Vite build, not the dev server. Run `npm run build:testing` before any browser test run, and again after each frontend change, or the tests will exercise stale assets.
