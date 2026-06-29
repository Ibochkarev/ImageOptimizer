import { defineComponent, computed, onMounted, ref } from 'vue'
import { Button, ProgressBar, ToggleButton } from 'primevue'
import StatCard from '../ui/StatCard.js'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import { useQueuePolling } from '../composables/useQueuePolling.js'
import { getConfig, lex, lexFormat } from '../request.js'

export default defineComponent({
  name: 'Dashboard',
  components: { Button, ProgressBar, ToggleButton, StatCard },
  setup() {
    const api = useImageOptimizerApi()
    const { notifyError, notifySuccess, notifyWarn } = useImageOptimizerNotify()
    const loading = ref(false)
    const summary = ref({ queue: {}, readiness: 0, encoders: {} })
    const canRun = computed(() => Number(getConfig().permissions?.run) === 1)

    const counts = computed(() => summary.value.queue || {})
    const pending = computed(() => Number(counts.value.pending || 0))
    const done = computed(() => Number(counts.value.done || 0))
    const failed = computed(() => Number(counts.value.failed || 0))
    const skipped = computed(() => Number(counts.value.skipped || 0))
    const processing = computed(() => Number(counts.value.processing || 0))
    const total = computed(() => pending.value + done.value + failed.value + skipped.value + processing.value)
    const progress = computed(() => total.value > 0 ? Math.round((done.value / total.value) * 100) : 0)

    async function loadSummary() {
      loading.value = true
      try {
        const res = await api.statsSummary()
        summary.value = res.data || { queue: {}, readiness: 0 }
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    async function resetStuck() {
      try {
        const res = await api.queueResetStuck()
        notifySuccess(lexFormat('imageoptimizer.dashboard.reset_stuck_done', res.data?.reset ?? 0))
        await loadSummary()
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function processQueue() {
      loading.value = true
      try {
        const res = await api.queueProcess()
        const pendingLeft = Number(res.data?.queue?.pending ?? 0)
        notifySuccess(lexFormat('imageoptimizer.queue.process_done', res.data?.processed ?? 0, pendingLeft))
        if (res.data?.time_budget_exceeded) {
          notifyWarn(lex('imageoptimizer.queue.process_time_budget'))
        }
        await loadSummary()
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    const polling = useQueuePolling(loadSummary)

    onMounted(loadSummary)

    return {
      lex,
      loading,
      pending,
      done,
      failed,
      skipped,
      processing,
      progress,
      summary,
      canRun,
      resetStuck,
      processQueue,
      polling,
    }
  },
  template: `
    <div class="imageoptimizer-tab-panel">
      <div class="flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="font-semibold text-lg">{{ lex('imageoptimizer.dashboard.title') }}</div>
        <ToggleButton
          :modelValue="polling.enabled.value"
          :onLabel="lex('imageoptimizer.live.on')"
          :offLabel="lex('imageoptimizer.live.off')"
          onIcon="pi pi-bolt"
          offIcon="pi pi-bolt"
          @update:modelValue="(v) => v ? polling.start() : polling.stop()" />
      </div>
      <div class="grid">
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.pending')" :value="pending"
            icon="pi pi-clock" :loading="loading" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.done')" :value="done"
            icon="pi pi-check" :loading="loading" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.failed')" :value="failed"
            icon="pi pi-times" :loading="loading" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.skipped')" :value="skipped"
            icon="pi pi-forward" :loading="loading" />
        </div>
      </div>
      <div class="grid mt-1">
        <div class="col-12 md:col-8">
          <StatCard :label="lex('imageoptimizer.dashboard.progress')" :value="progress + '%'" icon="pi pi-chart-line" :loading="loading">
            <ProgressBar :value="progress" class="mt-2" />
          </StatCard>
        </div>
        <div class="col-12 md:col-4">
          <StatCard :label="lex('imageoptimizer.dashboard.readiness')" :value="summary.readiness + '%'"
            icon="pi pi-server" :loading="loading" />
        </div>
      </div>
      <div v-if="canRun" class="flex flex-wrap gap-2 mt-2">
        <Button :label="lex('imageoptimizer.queue.process')" icon="pi pi-play" severity="info"
          :loading="loading" :disabled="pending === 0" @click="processQueue" />
        <Button :label="lex('imageoptimizer.queue.reset_stuck')" icon="pi pi-refresh" severity="secondary"
          @click="resetStuck" />
      </div>
    </div>
  `,
})
