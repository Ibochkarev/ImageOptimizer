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
    const progress = computed(() => {
      if (total.value <= 0) {
        return 0;
      }
      // Count finished work (done + failed + skipped), not only successful done.
      const finished = done.value + failed.value + skipped.value;
      return Math.min(100, Math.round((finished / total.value) * 100));
    });
    const processButtonLabel = computed(() => {
      if (queueProcessor.running.value) {
        return lexFormat(
          "imageoptimizer.queue.processing",
          queueProcessor.pendingLeft.value,
        );
      }
      return lex("imageoptimizer.queue.process");
    });
    const batchProgressText = computed(() => {
      if (!queueProcessor.running.value) {
        return "";
      }
      return lexFormat(
        "imageoptimizer.queue.batch_progress",
        queueProcessor.totalProcessed.value,
        pending.value,
        failed.value,
      );
    });
    const statsLoading = computed(
      () => loading.value && !queueProcessor.running.value,
    );

    async function loadSummary({ quiet = false } = {}) {
      if (!quiet) {
        loading.value = true;
      }
      try {
        const res = await api.statsSummary();
        const data = res.data || { queue: {}, readiness: 0 };
        if (quiet) {
          // Live / in-process polls: refresh queue counts only. Readiness is static.
          summary.value = {
            ...summary.value,
            queue: data.queue || summary.value.queue,
          };
        } else {
          summary.value = data;
        }
      } catch (e) {
        if (!quiet) {
          notifyError(e.message);
        }
      } finally {
        if (!quiet) {
          loading.value = false;
        }
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
          if (!cancelled && pendingLeft === 0) {
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
      lexFormat,
      loading,
      pending,
      done,
      failed,
      skipped,
      processing,
      progress,
      batchProgressText,
      summary,
      canRun,
      resetStuck,
      processQueue,
      stopProcessing,
      queueProcessor,
      processButtonLabel,
      statsLoading,
      polling,
    };
  },
  template: `
    <div class="imageoptimizer-tab-panel">
      <div class="flex flex-wrap align-items-center justify-content-between gap-1 mb-2">
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
            tone="warn" icon="pi pi-clock" :loading="statsLoading" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.done')" :value="done"
            tone="success" icon="pi pi-check" :loading="statsLoading" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.failed')" :value="failed"
            tone="danger" icon="pi pi-times" :loading="statsLoading" />
        </div>
        <div class="col-12 md:col-3">
          <StatCard :label="lex('imageoptimizer.status.skipped')" :value="skipped"
            tone="muted" icon="pi pi-forward" :loading="statsLoading" />
        </div>
      </div>
      <div class="grid mt-1">
        <div class="col-12 md:col-8">
          <StatCard :label="lex('imageoptimizer.dashboard.progress')" :value="progress + '%'" icon="pi pi-chart-line" :loading="statsLoading">
            <ProgressBar :value="progress" class="mt-2" />
            <div v-if="queueProcessor.running.value" class="text-sm text-color-secondary mt-2">
              {{ batchProgressText }}
            </div>
          </StatCard>
        </div>
        <div class="col-12 md:col-4">
          <StatCard :label="lex('imageoptimizer.dashboard.readiness')" :value="summary.readiness + '%'"
            tone="muted" secondary icon="pi pi-server" :loading="statsLoading" />
        </div>
      </div>
      <div v-if="canRun" class="flex flex-wrap gap-1 mt-2">
        <Button v-if="!queueProcessor.running.value"
          :label="processButtonLabel" icon="pi pi-play"
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
