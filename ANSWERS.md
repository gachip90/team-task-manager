# ANSWERS

## 1. Performance: optimizing `GET /api/tasks` for 1,000,000+ records

- Use server-side pagination, preferably cursor pagination or keyset pagination instead of returning all rows with `get()`.
- Add indexes for frequently filtered and sorted columns such as `status`, `assigned_to`, `created_at`, and `due_date`.
- Consider composite indexes based on real query patterns, for example `(assigned_to, status, created_at)`.
- Select only the columns needed by the UI instead of using `SELECT *`.
- Avoid N+1 queries by using eager loading with limited columns, for example `with('assignee:id,name,email,role')`.
- Push search, filtering, and sorting to the database instead of processing large datasets on the client.
- Cache common queries or summary data, with proper invalidation when tasks are created, updated, or deleted.
- Split heavy use cases into dedicated endpoints, for example `/api/my-tasks`, `/api/tasks/summary`, or `/api/tasks/export`.
- Move expensive exports, reports, and statistics to background jobs or queues.
- Use `EXPLAIN`, slow query logs, and application metrics to tune indexes based on real production data.
- For very large datasets, consider partitioning by date/status or using read replicas for read-heavy workloads.

## 2. Security: common risks and mitigations in a Laravel API + Vue SPA

- SQL Injection:
  - Use Eloquent or Query Builder with parameter binding.
  - Avoid manually concatenating raw SQL strings.
  - Validate incoming data with Form Requests.
- XSS:
  - Vue escapes interpolated values by default.
  - Avoid `v-html` with untrusted content.
  - Sanitize rich text content if rendering HTML is required.
- CSRF:
  - With Bearer token APIs, CSRF is less relevant than with cookie-based sessions, but CORS still needs strict configuration.
  - If using Sanctum SPA cookie/session mode, enable the standard Laravel Sanctum CSRF flow.
- Token leakage:
  - Never log access tokens.
  - Use HTTPS in production.
  - Consider httpOnly secure cookies instead of `localStorage` for production-grade auth.
- Broken access control:
  - Enforce permissions on the backend with Policies and Middleware.
  - Do not rely on frontend route guards or hidden buttons for security.
  - Regular users should only access tasks assigned to themselves.
- Brute-force login:
  - Apply rate limiting to `POST /api/login`.
  - Return generic login errors without revealing whether an email exists.
- Mass assignment:
  - Define explicit `$fillable` fields on models.
  - Only persist validated data from Form Requests.
- Misconfigured CORS:
  - Allow only trusted frontend origins in production.
  - Avoid wildcard origins for authenticated endpoints.
- Sensitive data exposure:
  - Do not return password hashes, internal tokens, or unnecessary user fields.
  - Set `APP_DEBUG=false` in production to avoid exposing stack traces.

## 3. TypeScript & Vue

- TypeScript catches many data-shape and type errors before runtime.
- Props, emits, store state, and API responses become easier to understand and refactor.
- IDE autocomplete and navigation are much better for components, composables, and API objects.
- Shared interfaces such as `Task`, `User`, and `TaskPayload` make the contract between the UI and service layer clearer.
- It improves maintainability as the Vue application grows.

Example of strongly typed props with `<script setup lang="ts">`:

```vue
<script setup lang="ts">
type TaskStatus = "todo" | "in_progress" | "done";

interface TaskCardProps {
  id: number;
  title: string;
  status: TaskStatus;
  assigneeName?: string;
  dueDate: string | null;
}

const props = defineProps<TaskCardProps>();
</script>

<template>
  <article>
    <h3>{{ props.title }}</h3>
    <p>Status: {{ props.status }}</p>
    <p v-if="props.assigneeName">Assignee: {{ props.assigneeName }}</p>
    <p v-if="props.dueDate">Due: {{ props.dueDate }}</p>
  </article>
</template>
```
