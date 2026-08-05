import { ref } from 'vue'
import { useImageOptimizerApi } from './useImageOptimizerApi.js'

const WORKER_BUSY_RETRIES = 3
const WORKER_BUSY_DELAY_MS = 2000

function sleep(ms) {
  return new Promise((resolve) => {
    setTimeout(resolve, ms)
  })
}

export function useQueueProcessor() {
  const api = useImageOptimizerApi()
  const running = ref(false)
  const cancelled = ref(false)
  const totalProcessed = ref(0)
  const pendingLeft = ref(0)
  const timeBudgetHit = ref(false)

  function stop() {
    cancelled.value = true
  }

  async function processBatchWithRetry() {
    let lastError = null
    for (let attempt = 0; attempt < WORKER_BUSY_RETRIES; attempt++) {
      try {
        return await api.queueProcess()
      } catch (e) {
        lastError = e
        const isBusy = e.status === 409
        if (!isBusy || attempt === WORKER_BUSY_RETRIES - 1) {
          throw e
        }
        await sleep(WORKER_BUSY_DELAY_MS)
      }
    }
    throw lastError
  }

  async function runUntilDone({ onBatch, onDone, onError } = {}) {
    if (running.value) {
      return
    }

    running.value = true
    cancelled.value = false
    totalProcessed.value = 0
    pendingLeft.value = 0
    timeBudgetHit.value = false

    try {
      while (!cancelled.value) {
        const res = await processBatchWithRetry()
        const processed = Number(res.data?.processed ?? 0)
        const pending = Number(res.data?.queue?.pending ?? 0)

        totalProcessed.value += processed
        pendingLeft.value = pending

        if (res.data?.time_budget_exceeded) {
          timeBudgetHit.value = true
        }

        if (onBatch) {
          await onBatch(res)
        }

        if (pending === 0) {
          break
        }
        if (processed === 0) {
          break
        }
      }

      if (onDone) {
        await onDone({
          totalProcessed: totalProcessed.value,
          pendingLeft: pendingLeft.value,
          cancelled: cancelled.value,
          timeBudgetHit: timeBudgetHit.value,
        })
      }
    } catch (e) {
      if (onError) {
        onError(e)
      } else {
        throw e
      }
    } finally {
      running.value = false
    }
  }

  return {
    running,
    cancelled,
    totalProcessed,
    pendingLeft,
    timeBudgetHit,
    runUntilDone,
    stop,
  }
}
