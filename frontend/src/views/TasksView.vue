<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { AxiosError } from 'axios'
import TaskForm from '@/components/TaskForm.vue'
import { useTasksStore } from '@/stores/tasks'
import type { Task, TaskPayload, TaskStatus } from '@/types/api'

const tasksStore = useTasksStore()

const searchInput = ref('')
const appliedSearch = ref('')
const statusFilter = ref<TaskStatus | 'all'>('all')
const isDialogOpen = ref(false)
const isSaving = ref(false)
const selectedTask = ref<Task | null>(null)
const errorMessage = ref('')

const statusOptions: Array<{ title: string; value: TaskStatus | 'all' }> = [
  { title: 'All statuses', value: 'all' },
  { title: 'Todo', value: 'todo' },
  { title: 'In progress', value: 'in_progress' },
  { title: 'Done', value: 'done' },
]

const headers = [
  { title: 'Title', key: 'title' },
  { title: 'Assignee', key: 'assignee' },
  { title: 'Status', key: 'status' },
  { title: 'Due date', key: 'due_date' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const filteredTasks = computed(() => {
  const keyword = appliedSearch.value.trim().toLowerCase()

  if (!keyword) {
    return tasksStore.tasks
  }

  return tasksStore.tasks.filter((task) => task.title.toLowerCase().includes(keyword))
})

onMounted(() => {
  void fetchTasks()
})

async function fetchTasks(): Promise<void> {
  errorMessage.value = ''

  try {
    await tasksStore.fetchTasks({
      status: statusFilter.value === 'all' ? undefined : statusFilter.value,
    })
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  }
}

function applySearch(): void {
  appliedSearch.value = searchInput.value
}

function openCreateDialog(): void {
  selectedTask.value = null
  isDialogOpen.value = true
}

function openEditDialog(task: Task): void {
  selectedTask.value = task
  isDialogOpen.value = true
}

async function saveTask(payload: TaskPayload): Promise<void> {
  isSaving.value = true
  errorMessage.value = ''

  try {
    if (selectedTask.value) {
      await tasksStore.updateTask(selectedTask.value.id, payload)
    } else {
      await tasksStore.createTask(payload)
    }

    isDialogOpen.value = false
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  } finally {
    isSaving.value = false
  }
}

async function deleteTask(task: Task): Promise<void> {
  const shouldDelete = window.confirm(`Delete task "${task.title}"?`)

  if (!shouldDelete) {
    return
  }

  errorMessage.value = ''

  try {
    await tasksStore.deleteTask(task.id)
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  }
}

function formatStatus(status: TaskStatus): string {
  const labels: Record<TaskStatus, string> = {
    todo: 'Todo',
    in_progress: 'In progress',
    done: 'Done',
  }

  return labels[status]
}

function statusColor(status: TaskStatus): string {
  const colors: Record<TaskStatus, string> = {
    todo: 'blue-grey',
    in_progress: 'primary',
    done: 'secondary',
  }

  return colors[status]
}

function formatDate(date: string | null): string {
  if (!date) {
    return 'No due date'
  }

  return new Intl.DateTimeFormat('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

function getErrorMessage(error: unknown): string {
  if (error instanceof AxiosError && error.response?.status === 403) {
    return 'You do not have permission to perform this action.'
  }

  if (error instanceof AxiosError && error.response?.status === 422) {
    return 'Please check the task details and try again.'
  }

  return 'Unable to load task data right now.'
}
</script>

<template>
  <main class="tasks-page">
    <section class="page-header">
      <div>
        <h1>Tasks</h1>
        <p>{{ filteredTasks.length }} visible tasks</p>
      </div>

      <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreateDialog">
        New task
      </v-btn>
    </section>

    <v-alert v-if="errorMessage" class="mb-4" density="compact" type="error" variant="tonal">
      {{ errorMessage }}
    </v-alert>

    <v-sheet class="task-toolbar" border rounded="lg">
      <v-text-field
        v-model="searchInput"
        density="comfortable"
        hide-details
        label="Search title"
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        @keydown.enter="applySearch"
      />

      <v-select
        v-model="statusFilter"
        density="comfortable"
        hide-details
        :items="statusOptions"
        label="Status"
        variant="outlined"
        @update:model-value="fetchTasks"
      />

      <v-btn icon="mdi-refresh" title="Refresh" variant="text" @click="fetchTasks" />
    </v-sheet>

    <v-sheet border rounded="lg">
      <v-data-table
        :headers="headers"
        :items="filteredTasks"
        :items-per-page="10"
        :loading="tasksStore.isLoading"
        item-value="id"
      >
        <template #item.assignee="{ item }">
          <span>{{ item.assignee?.name ?? `User #${item.assigned_to}` }}</span>
        </template>

        <template #item.status="{ item }">
          <v-chip :color="statusColor(item.status)" size="small" variant="tonal">
            {{ formatStatus(item.status) }}
          </v-chip>
        </template>

        <template #item.due_date="{ item }">
          {{ formatDate(item.due_date) }}
        </template>

        <template #item.actions="{ item }">
          <div class="action-buttons">
            <v-btn icon="mdi-pencil" size="small" title="Edit" variant="text" @click="openEditDialog(item)" />
            <v-btn
              color="error"
              icon="mdi-delete-outline"
              size="small"
              title="Delete"
              variant="text"
              @click="deleteTask(item)"
            />
          </div>
        </template>
      </v-data-table>
    </v-sheet>

    <TaskForm
      v-model="isDialogOpen"
      :is-saving="isSaving"
      :task="selectedTask"
      @save="saveTask"
    />
  </main>
</template>

<style scoped>
.tasks-page {
  margin: 0 auto;
  max-width: 1180px;
  padding: 32px 24px;
}

.page-header {
  align-items: center;
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
}

.page-header h1 {
  color: #172033;
  font-size: 28px;
  font-weight: 700;
  line-height: 1.2;
  margin: 0;
}

.page-header p {
  color: #667085;
  font-size: 14px;
  margin: 4px 0 0;
}

.task-toolbar {
  align-items: center;
  display: grid;
  gap: 16px;
  grid-template-columns: minmax(220px, 1fr) minmax(180px, 240px) auto;
  margin-bottom: 16px;
  padding: 16px;
}

.action-buttons {
  display: flex;
  gap: 4px;
}

@media (max-width: 720px) {
  .page-header {
    align-items: flex-start;
    gap: 16px;
    flex-direction: column;
  }

  .task-toolbar {
    grid-template-columns: 1fr;
  }
}
</style>