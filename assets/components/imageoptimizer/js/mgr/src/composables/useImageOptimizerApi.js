import { apiRequest } from '../request.js'

export function useImageOptimizerApi() {
  return {
    statsSummary: () => apiRequest('stats/summary'),
    queueList: (params) => apiRequest('queue/list', params),
    queueRetry: (ids) => apiRequest('queue/retry', { ids: ids.join(',') }),
    queueRebuild: (params) => apiRequest('queue/rebuild', params),
    queueClear: (params) => apiRequest('queue/clear', params),
    queueResetStuck: () => apiRequest('queue/reset_stuck'),
    queueProcess: (params) => apiRequest('queue/process', params),
    settingsGet: () => apiRequest('settings/get'),
    settingsUpdate: (settings) => apiRequest('settings/update', { settings: JSON.stringify(settings) }),
    serverCheck: () => apiRequest('server/check'),
    compatibilityList: () => apiRequest('compatibility/list'),
  }
}
