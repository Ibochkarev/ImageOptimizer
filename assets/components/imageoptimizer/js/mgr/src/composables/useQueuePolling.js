import { onUnmounted, ref } from 'vue'

const DEFAULT_INTERVAL_MS = 5000

export function useQueuePolling(callback, intervalMs = DEFAULT_INTERVAL_MS) {
  const enabled = ref(false)
  let timer = null

  function stop() {
    if (timer) {
      clearInterval(timer)
      timer = null
    }
    enabled.value = false
  }

  function start() {
    stop()
    enabled.value = true
    timer = setInterval(() => {
      callback()
    }, intervalMs)
  }

  function toggle() {
    if (enabled.value) {
      stop()
    } else {
      start()
    }
  }

  onUnmounted(stop)

  return { enabled, start, stop, toggle }
}
