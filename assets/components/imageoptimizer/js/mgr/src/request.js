export function getConfig() {
  if (typeof window !== 'undefined' && window.imageoptimizerConfig) {
    return window.imageoptimizerConfig
  }
  return { connectorUrl: '', lexicon: {}, permissions: {}, modAuth: '' }
}

function decodeLexiconText(text) {
  if (!text.includes('&lt;') && !text.includes('&gt;') && !text.includes('&amp;')) {
    return text
  }
  return text.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&')
}

export function lex(key, fallback = '') {
  const cfg = getConfig()
  const value = cfg.lexicon?.[key]
  const raw = value != null && value !== '' ? value : fallback || key
  return decodeLexiconText(raw)
}

/** Подстановка %s в строках лексикона (MODX-стиль). */
export function lexFormat(key, ...args) {
  let text = lex(key)
  for (const arg of args) {
    text = text.replace('%s', String(arg))
  }
  return text
}

export async function apiRequest(action, params = {}, options = {}) {
  const cfg = getConfig()
  const connectorUrl = String(cfg.connectorUrl || '').trim()
  if (!connectorUrl) {
    throw new Error(lex('imageoptimizer.error.connector_missing', 'Connector URL is not configured. Reload the manager page.'))
  }

  const body = new URLSearchParams({ action, ...params })
  if (cfg.modAuth) {
    body.set('HTTP_MODAUTH', cfg.modAuth)
  }

  const headers = {
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-Requested-With': 'XMLHttpRequest',
  }
  if (cfg.modAuth) {
    headers.Modauth = cfg.modAuth
  }

  const response = await fetch(connectorUrl, {
    method: options.method || 'POST',
    headers,
    credentials: 'same-origin',
    body: body.toString(),
  })

  const raw = await response.text()
  let data
  try {
    data = raw ? JSON.parse(raw) : {}
  } catch {
    const snippet = raw.replace(/\s+/g, ' ').trim().slice(0, 120)
    throw new Error(
      lex(
        'imageoptimizer.error.invalid_response',
        `Invalid JSON from connector (${action}). Reload the page or check PHP logs. Response: ${snippet}`,
      ),
    )
  }

  if (!response.ok || data.success === false) {
    throw new Error(data.message || lex('imageoptimizer.error.request_failed', `Request failed: ${action}`))
  }
  return data
}
