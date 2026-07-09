<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import type { ApiDataResponse, Task, TaskPayload, TaskStatus, User } from '@/types/api'

const props = defineProps<{
  modelValue: boolean
  task: Task | null
  isSaving?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  save: [payload: TaskPayload]
}>()

const authStore = useAuthStore()
const statusOptions: Array<{ title: string; value: TaskStatus }> = [
  { title: 'Todo', value: 'todo' },
  { title: 'In progress', value: 'in_progress' },
  { title: 'Done', value: 'done' },
]

const form = reactive<{
  title: string
  description: string
  status: TaskStatus
  assigned_to: number | null
  due_date: string
}>({
  title: '',
  description: '',
  status: 'todo',
  assigned_to: null,
  due_date: '',
})
const assignees = ref<User[]>([])
const isLoadingAssignees = ref(false)
const isDateMenuOpen = ref(false)
const selectedDueDate = ref<Date | null>(null)

const dialogTitle = computed(() => (props.task ? 'Edit task' : 'Create task'))
const canEditAssignee = computed(() => authStore.user?.role === 'admin')
const assigneeOptions = computed(() =>
  assignees.value.map((user) => ({
    title: user.name,
    value: user.id,
  })),
)
const canSave = computed(
  () =>
    form.title.trim() !== '' &&
    form.description.trim() !== '' &&
    (!canEditAssignee.value || form.assigned_to !== null),
)

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      fillForm()
      void fetchAssignees()
    }
  },
)

function fillForm(): void {
  form.title = props.task?.title ?? ''
  form.description = props.task?.description ?? ''
  form.status = props.task?.status ?? 'todo'
  form.assigned_to = props.task?.assigned_to ?? (canEditAssignee.value ? 2 : authStore.user?.id ?? null)
  form.due_date = props.task?.due_date ?? ''
  selectedDueDate.value = form.due_date ? parseDate(form.due_date) : null
}

function closeDialog(): void {
  emit('update:modelValue', false)
}

async function fetchAssignees(): Promise<void> {
  if (!canEditAssignee.value || assignees.value.length > 0) {
    return
  }

  isLoadingAssignees.value = true

  try {
    const { data } = await api.get<ApiDataResponse<User[]>>('/users')
    assignees.value = data.data

    if (form.assigned_to === null && data.data[0]) {
      form.assigned_to = data.data[0].id
    }
  } finally {
    isLoadingAssignees.value = false
  }
}

function parseDate(date: string): Date {
  const [year, month, day] = date.split('-').map(Number)

  if (!year || !month || !day) {
    return new Date()
  }

  return new Date(year, month - 1, day)
}

function formatDateForApi(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

function updateDueDate(date: Date | null): void {
  selectedDueDate.value = date
  form.due_date = date ? formatDateForApi(date) : ''
  isDateMenuOpen.value = false
}

function clearDueDate(): void {
  updateDueDate(null)
}

function submitForm(): void {
  if (!canSave.value) {
    return
  }

  const payload: TaskPayload = {
    title: form.title.trim(),
    description: form.description.trim(),
    status: form.status,
    due_date: form.due_date || null,
  }

  if (canEditAssignee.value && form.assigned_to !== null) {
    payload.assigned_to = Number(form.assigned_to)
  }

  emit('save', payload)
}
</script>

<template>
  <v-dialog
    :model-value="modelValue"
    max-width="640"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title class="dialog-title">
        {{ dialogTitle }}
      </v-card-title>

      <v-card-text>
        <v-form @submit.prevent="submitForm">
          <v-text-field v-model="form.title" label="Title" variant="outlined" />

          <v-textarea
            v-model="form.description"
            auto-grow
            label="Description"
            rows="3"
            variant="outlined"
          />

          <div class="form-grid">
            <v-select
              v-model="form.status"
              :items="statusOptions"
              label="Status"
              variant="outlined"
            />

            <v-menu v-model="isDateMenuOpen" :close-on-content-click="false" min-width="auto">
              <template #activator="{ props: menuProps }">
                <v-text-field
                  v-bind="menuProps"
                  :model-value="form.due_date"
                  append-inner-icon="mdi-calendar"
                  clearable
                  label="Due date"
                  readonly
                  variant="outlined"
                  @click:clear="clearDueDate"
                />
              </template>

              <v-date-picker
                :model-value="selectedDueDate"
                title="Select due date"
                @update:model-value="updateDueDate"
              />
            </v-menu>
          </div>

          <v-select
            v-if="canEditAssignee"
            v-model="form.assigned_to"
            :items="assigneeOptions"
            :loading="isLoadingAssignees"
            label="Assignee"
            variant="outlined"
          />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="closeDialog">Cancel</v-btn>
        <v-btn
          color="primary"
          :disabled="!canSave"
          :loading="isSaving"
          prepend-icon="mdi-content-save"
          @click="submitForm"
        >
          Save
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.dialog-title {
  font-size: 18px;
  font-weight: 700;
}

.form-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
