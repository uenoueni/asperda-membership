---
name: asperda-stack
description: >
  Technical conventions and coding standards for the ASPERDA membership registration system.
  Use this skill for ALL coding tasks on the ASPERDA project — backend (Laravel 11 REST API),
  frontend (Vue 3 + Vite PWA), or full-stack features. Triggers include: writing controllers,
  API endpoints, Vue components, composables, form handling, dropdown/select components,
  Midtrans integration, survey/escalation logic, certificate generation, queue jobs, migrations,
  or any task that produces code for this project. Always use this skill before writing any
  ASPERDA code to ensure consistency across the codebase.
---

# ASPERDA Stack — Technical Conventions

This skill enforces consistent patterns across the ASPERDA codebase.
Read the relevant reference file for the layer you are working on.

## Reference Files

| File | Read when working on |
|---|---|
| `references/laravel.md` | Controllers, Models, Migrations, Jobs, Middleware, Policies, API responses |
| `references/vue.md` | Vue components, composables, routing, state, form handling |
| `references/api-contract.md` | Any endpoint that is consumed by the frontend — request/response shape, error format, pagination |
| `references/patterns.md` | Cross-cutting patterns: dropdown lazy-load, status enums, wilindo integration, Midtrans webhook, dompdf certificate |

**Always read the reference file before writing code.** Do not rely on memory — conventions are specific to this project.

## Quick Rules (apply to all layers)

1. **API calls are always via the `useApi()` composable** — never raw `fetch` or `axios` in components.
2. **Dropdowns with external data are always lazy-loaded** — no data preloaded on page mount unless the list has fewer than 20 static items.
3. **All enums live in one place** — `app/Enums/` on the backend, `src/constants/enums.js` on the frontend. Never hardcode string values inline.
4. **API responses always follow the standard envelope** — `{ success, data, message, errors?, meta? }`. Never return bare objects.
5. **RBAC scope is enforced in Policy, not in the controller** — the controller calls `$this->authorize()` and nothing more. The Policy handles the wilayah filter.
6. **Queue jobs for anything async** — email, PDF generation, escalation. Never do these synchronously in a controller.
7. **Wilayah dropdowns always call the API** — never import wilindo data directly into Vue. Use the three-tier cascade: provinsi → kota → kecamatan.
