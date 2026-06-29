import { defineComponent, computed, onMounted, ref } from 'vue'
import { DataTable, Column, Button, Select, ToggleButton } from 'primevue'
import FilterToolbar from '../ui/FilterToolbar.js'
import EmptyState from '../ui/EmptyState.js'
import QueueStatusTag from '../ui/StatusTag.js'
import RebuildQueueDialog from './RebuildQueueDialog.js'
import ClearVariantsDialog from './ClearVariantsDialog.js'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import { useQueuePolling } from '../composables/useQueuePolling.js'
import { getConfig, lex, lexFormat } from '../request.js'
import { MGR_DATATABLE_SCROLL_HEIGHT } from '../layout.js'

const STATUS_OPTIONS = [
  { value: '', labelKey: 'imageoptimizer.filter.all_statuses' },
  { value: 'pending', labelKey: 'imageoptimizer.status.pending' },
  { value: 'processing', labelKey: 'imageoptimizer.status.processing' },
  { value: 'done', labelKey: 'imageoptimizer.status.done' },
  { value: 'failed', labelKey: 'imageoptimizer.status.failed' },
  { value: 'skipped', labelKey: 'imageoptimizer.status.skipped' },
]

export default defineComponent({
  name: 'QueueGrid',
  components: {
    DataTable,
    Column,
    Button,
    Select,
    ToggleButton,
    FilterToolbar,
    EmptyState,
    QueueStatusTag,
    RebuildQueueDialog,
    ClearVariantsDialog,
  },
  setup() {
    const api = useImageOptimizerApi()
    const { notifyError, notifySuccess, notifyWarn } = useImageOptimizerNotify()
    const rows = ref([])
    const total = ref(0)
    const loading = ref(false)
    const search = ref('')
    const status = ref('')
    const first = ref(0)
    const pageSize = ref(50)
    const selected = ref([])
    const showRebuild = ref(false)
    const showClear = ref(false)
    const processing = ref(false)
    const canRun = computed(() => Number(getConfig().permissions?.run) === 1)
    const statusOptions = computed(() => STATUS_OPTIONS.map((o) => ({
      value: o.value,
      label: lex(o.labelKey),
    })))

    async function loadQueue() {
      loading.value = true
      try {
        const res = await api.queueList({
          offset: first.value,
          limit: pageSize.value,
          query: search.value,
          status: status.value,
        })
        rows.value = res.data || []
        total.value = Number(res.total ?? rows.value.length)
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    function onPage(event) {
      first.value = event.first
      pageSize.value = event.rows
      loadQueue()
    }

    function onSearch() {
      first.value = 0
      loadQueue()
    }

    function clearFilters() {
      search.value = ''
      status.value = ''
      first.value = 0
      loadQueue()
    }

    async function retrySelected() {
      const ids = selected.value.map((row) => row.id).filter(Boolean)
      if (ids.length === 0) {
        return
      }
      try {
        const res = await api.queueRetry(ids)
        notifySuccess(lexFormat('imageoptimizer.queue.retry_done', res.data?.updated ?? 0))
        selected.value = []
        await loadQueue()
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function processQueue() {
      processing.value = true
      try {
        const res = await api.queueProcess()
        const pendingLeft = Number(res.data?.queue?.pending ?? 0)
        notifySuccess(lexFormat('imageoptimizer.queue.process_done', res.data?.processed ?? 0, pendingLeft))
        if (res.data?.time_budget_exceeded) {
          notifyWarn(lex('imageoptimizer.queue.process_time_budget'))
        }
        await loadQueue()
      } catch (e) {
        notifyError(e.message)
      } finally {
        processing.value = false
      }
    }

    function formatBytes(bytes) {
      const n = Number(bytes)
      if (!n || n <= 0) {
        return '—'
      }
      if (n < 1024) {
        return `${n} B`
      }
      if (n < 1048576) {
        return `${(n / 1024).toFixed(1)} KB`
      }
      return `${(n / 1048576).toFixed(2)} MB`
    }

    const polling = useQueuePolling(loadQueue)

    onMounted(loadQueue)

    return {
      lex,
      rows,
      total,
      loading,
      search,
      status,
      first,
      pageSize,
      selected,
      showRebuild,
      showClear,
      processing,
      canRun,
      statusOptions,
      loadQueue,
      onPage,
      onSearch,
      clearFilters,
      retrySelected,
      processQueue,
      formatBytes,
      polling,
      scrollHeight: MGR_DATATABLE_SCROLL_HEIGHT,
    }
  },
  template: `
    <div class="imageoptimizer-tab-panel imageoptimizer-datatable-shell">
      <FilterToolbar
        :search="search"
        :searchPlaceholder="lex('imageoptimizer.filter.search')"
        :clearTitle="lex('imageoptimizer.filter.clear')"
        @update:search="search = $event"
        @search="onSearch"
        @clear="clearFilters">
        <template #start>
          <ToggleButton
            :modelValue="polling.enabled.value"
            :onLabel="lex('imageoptimizer.live.on')"
            :offLabel="lex('imageoptimizer.live.off')"
            onIcon="pi pi-bolt"
            offIcon="pi pi-bolt"
            @update:modelValue="(v) => v ? polling.start() : polling.stop()" />
          <Button v-if="canRun" :label="lex('imageoptimizer.queue.process')" icon="pi pi-play" severity="info"
            :loading="processing" @click="processQueue" />
          <Button v-if="canRun" :label="lex('imageoptimizer.queue.rebuild')" icon="pi pi-plus" severity="success"
            @click="showRebuild = true" />
          <Button v-if="canRun" :label="lex('imageoptimizer.queue.clear')" icon="pi pi-trash" severity="danger" outlined
            @click="showClear = true" />
          <Button v-if="canRun && selected.length" :label="lex('imageoptimizer.queue.retry')" icon="pi pi-replay"
            @click="retrySelected" />
        </template>
        <template #filters>
          <Select v-model="status" :options="statusOptions" optionLabel="label" optionValue="value"
            class="w-12rem" :placeholder="lex('imageoptimizer.filter.status')" @change="onSearch" />
        </template>
      </FilterToolbar>

      <DataTable
        v-model:selection="selected"
        :value="rows"
        :loading="loading"
        dataKey="id"
        lazy
        paginator
        :first="first"
        :rows="pageSize"
        :totalRecords="total"
        :scrollable="true"
        :scrollHeight="scrollHeight"
        stripedRows
        showGridlines
        size="small"
        @page="onPage">
        <template #empty>
          <EmptyState :title="lex('imageoptimizer.queue.empty')" :detail="lex('imageoptimizer.queue.empty_detail')" icon="pi pi-images">
            <Button v-if="canRun" :label="lex('imageoptimizer.queue.rebuild')" icon="pi pi-plus" @click="showRebuild = true" />
          </EmptyState>
        </template>
        <Column selectionMode="multiple" headerStyle="width: 3rem" />
        <Column field="id" :header="lex('imageoptimizer.col.id')" style="width: 4rem" />
        <Column field="source" :header="lex('imageoptimizer.col.source')" style="width: 5rem" />
        <Column field="path" :header="lex('imageoptimizer.col.path')">
          <template #body="{ data }">
            <span class="text-sm">{{ data.path }}</span>
          </template>
        </Column>
        <Column field="format" :header="lex('imageoptimizer.col.format')" style="width: 5rem" />
        <Column field="width" :header="lex('imageoptimizer.col.width')" style="width: 5rem" />
        <Column field="status" :header="lex('imageoptimizer.col.status')" style="width: 7rem">
          <template #body="{ data }">
            <QueueStatusTag :status="data.status" />
          </template>
        </Column>
        <Column :header="lex('imageoptimizer.col.sizes')" style="width: 8rem">
          <template #body="{ data }">
            {{ formatBytes(data.original_size) }} → {{ formatBytes(data.converted_size) }}
          </template>
        </Column>
        <Column field="error" :header="lex('imageoptimizer.col.error')" />
      </DataTable>

      <RebuildQueueDialog v-model:visible="showRebuild" @done="loadQueue" />
      <ClearVariantsDialog v-model:visible="showClear" @done="loadQueue" />
    </div>
  `,
})
