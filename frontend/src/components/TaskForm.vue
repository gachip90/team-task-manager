<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { Task, TaskPayload, TaskStatus } from '@/types/api'

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

const dialogTitle = computed(() => (props.task ? 'Edit task' : 'Create task'))
const canEditAssignee = computed(() => authStore.user?.role === 'admin')
const canSave = computed(() => form.title.trim() !== '' && form.description.trim() !== '')

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      fillForm()
    }
  },
)

function fillForm(): void {
  form.title = props.task?.title ?? ''
  form.description = props.task?.description ?? ''
  form.status = props.task?.status ?? 'todo'
  form.assigned_to = props.task?.assigned_to ?? authStore.user?.id ?? null
  form.due_date = props.task?.due_date ?? ''
}

function closeDialog(): void {
  emit('update:modelValue', false)
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

            <v-text-field v-model="form.due_date" label="Due date" type="date" variant="outlined" />
          </div>

          <v-text-field
            v-if="canEditAssignee"
            v-model.number="form.assigned_to"
            label="Assignee user ID"
            min="1"
            type="number"
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
