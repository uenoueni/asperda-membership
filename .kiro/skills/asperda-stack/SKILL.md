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
8. **Dropdowns always use lazy-load / server-side loading** — never pre-fetch or embed option lists at page mount. Options are fetched on-demand (on open or on search input) to avoid large payloads and keep initial load fast.
9. **Always add loading animations** — every page transition, component mount, and dropdown open state must show a loading indicator (skeleton, spinner, or shimmer). Never render blank space while data is being fetched.
10. **Always disable the triggering button during async operations** — any button that fires an API call, form submit, file upload, or any async action must be `disabled` (and visually indicate loading) from the moment it is clicked until the operation resolves or rejects. This prevents duplicate submissions.
11. **Always update the PRD when the flow changes** — if any task introduces a change to the application flow, API contract, or business logic that differs from the original spec, update the relevant PRD/reference file in `docs/` or `references/` immediately before finishing the task. Never leave the PRD out of sync with the implementation.
12. **Always mark completed tasks in `docs/PROGRESS.md`** — when a task is finished, update its status to `✅` in `docs/PROGRESS.md` and update the summary table percentages. Do this as the final step of every task, without exception.
