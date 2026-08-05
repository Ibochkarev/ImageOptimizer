import { defineComponent, computed, onMounted, ref } from 'vue'
import { Button, ProgressBar, ToggleButton } from 'primevue'
import StatCard from '../ui/StatCard.js'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import { useQueuePolling } from '../composables/useQueuePolling.js'
import { useQueueProcessor } from "../composables/useQueueProcessor.js";
import { getConfig, lex, lexFormat } from '../request.js'

export default defineComponent({
  name: "Dashboard",
  components: { Button, ProgressBar, ToggleButton, StatCard },
  setup() {
    const api = useImageOptimizerApi();
    const { notifyError, notifySuccess, notifyWarn } =
      useImageOptimizerNotify();
    const loading = ref(false);
    const summary = ref({ queue: {}, readiness: 0, encoders: {} });
    const canRun = computed(() => Number(getConfig().permissions?.run) === 1);
    const queueProcessor = useQueueProcessor();
    const pollingWasEnabled = ref(false);

    const counts = computed(() => summary.value.queue || {});
    const pending = computed(() => Number(counts.value.pending || 0));
    const done = computed(() => Number(counts.value.done || 0));
    const failed = computed(() => Number(counts.value.failed || 0));
    const skipped = computed(() => Number(counts.value.skipped || 0));
    const processing = computed(() => Number(counts.value.processing || 0));
    const total = computed(
      () =>
        pending.value +
        done.value +
        failed.value +
        skipped.value +
        processing.value,
    );
    const progress = computed(() =>
      total.value > 0 ? Math.round((done.value / total.value) * 100) : 0,
    );
    const processButtonLabel = computed(() => {
      if (queueProcessor.running.value) {
        return lexFormat(
          "imageoptimizer.queue.processing",
          queueProcessor.pendingLeft.value,
        );
      }
      return lex("imageoptimizer.queue.process");
    });

    async function loadSummary() {
      loading.value = true;
      try {
        const res = await api.statsSummary();
        summary.value = res.data || { queue: {}, readiness: 0 };
      } catch (e) {
        notifyError(e.message);
      } finally {
        loading.value = false;
      }
    }

    async function resetStuck() {
      try {
        const res = await api.queueResetStuck();
        notifySuccess(
          lexFormat(
            "imageoptimizer.dashboard.reset_stuck_done",
            res.data?.reset ?? 0,
          ),
        );
        await loadSummary();
      } catch (e) {
        notifyError(e.message);
      }
    }

    async function processQueue() {
      if (queueProcessor.running.value || pending.value === 0) {
        return;
      }

      pollingWasEnabled.value = polling.enabled.value;
      if (!polling.enabled.value) {
        polling.start();
      }

      await queueProcessor.runUntilDone({
        onBatch: async (res) => {
          if (res.data?.queue) {
            summary.value = {
              ...summary.value,
              queue: res.data.queue,
            };
          }
        },
        onDone: async ({
          totalProcessed,
          pendingLeft,
          cancelled,
          timeBudgetHit,
        }) => {
          await loadSummary();
          if (!pollingWasEnabled.value) {
            polling.stop();
          }
          if (cancelled) {
            notifyWarn(
              lexFormat(
                "imageoptimizer.queue.stopped",
                totalProcessed,
                pendingLeft,
              ),
            );
          } else if (pendingLeft === 0) {
            notifySuccess(
              lexFormat("imageoptimizer.queue.all_done", totalProcessed),
            );
          } else {
            notifyWarn(
              lexFormat(
                "imageoptimizer.queue.stopped",
                totalProcessed,
                pendingLeft,
              ),
            );
          }
          if (timeBudgetHit && pendingLeft > 0 && !cancelled) {
            notifyWarn(lex("imageoptimizer.queue.process_time_budget"));
          }
        },
        onError: (e) => {
          notifyError(e.message);
        },
      });
    }

    function stopProcessing() {
      queueProcessor.stop();
    }

    const polling = useQueuePolling(loadSummary);

    onMounted(loadSummary);

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
      stopProcessing,
      queueProcessor,
      processButtonLabel,
      polling,
    };
  },
  template: `
    <div class="imageoptimizer-tab-panel">
      <div class="flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="font-semibold text-lg">{{ lex('imageoptimizer.dashboard.title') }}</div>
        <div class="flex flex-column align-items-end gap-1">
          <ToggleButton
            :modelValue="polling.enabled.value"
            :onLabel="lex('imageoptimizer.live.on')"
            :offLabel="lex('imageoptimizer.live.off')"
            onIcon="pi pi-bolt"
            offIcon="pi pi-bolt"
            @update:modelValue="(v) => v ? polling.start() : polling.stop()" />
          <span class="text-xs text-color-secondary">{{ lex('imageoptimizer.live.hint') }}</span>
        </div>
      </div>
      <div class="grid">
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.pending')" :value="pending"
            icon="pi pi-clock" :loading="loading || queueProcessor.running.value" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.done')" :value="done"
            icon="pi pi-check" :loading="loading || queueProcessor.running.value" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.failed')" :value="failed"
            icon="pi pi-times" :loading="loading || queueProcessor.running.value" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.skipped')" :value="skipped"
            icon="pi pi-forward" :loading="loading || queueProcessor.running.value" />
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
        <Button v-if="!queueProcessor.running.value"
          :label="processButtonLabel" icon="pi pi-play" severity="info"
          :disabled="pending === 0" @click="processQueue" />
        <Button v-else
          :label="lex('imageoptimizer.queue.stop')" icon="pi pi-stop" severity="danger"
          @click="stopProcessing" />
        <Button :label="lex('imageoptimizer.queue.reset_stuck')" icon="pi pi-refresh" severity="secondary"
          :disabled="queueProcessor.running.value" @click="resetStuck" />
      </div>
    </div>
  `,
});
