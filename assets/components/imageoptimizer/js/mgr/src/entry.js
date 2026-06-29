if (typeof window !== 'undefined') {
  if (typeof window.imageoptimizerConfig !== 'object' || window.imageoptimizerConfig === null) {
    window.imageoptimizerConfig = { connectorUrl: '', lexicon: {}, permissions: {}, modAuth: '' }
  }
}

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { getPrimeVueLocale } from '@vuetools/usePrimeVueLocale'
import { PrimeVue, Aura, ToastService, ConfirmationService } from 'primevue'
import 'primeicons/primeicons.css'
import 'primeflex/primeflex.css'
import App from './App.js'
import { getConfig } from './request.js'

function resolveManagerCultureKey() {
  const cfg = getConfig()
  if (cfg.managerLanguage) {
    return String(cfg.managerLanguage).toLowerCase()
  }
  if (typeof window !== 'undefined') {
    return window.MODx?.cultureKey ?? window.MODx?.config?.cultureKey ?? 'en'
  }
  return 'en'
}

let appInstance = null
const SELECTOR = '#imageoptimizer-mgr-app'

if (typeof document !== 'undefined') {
  document.body.classList.add('imageoptimizer-manager-page')
}

function createVueApp() {
  const app = createApp(App)
  app.use(createPinia())
  app.use(PrimeVue, {
    theme: {
      preset: Aura,
      options: { darkModeSelector: '.modx-dark-mode' },
    },
    locale: getPrimeVueLocale(resolveManagerCultureKey()),
    ripple: true,
  })
  app.use(ToastService)
  app.use(ConfirmationService)
  return app
}

export function init(selector = SELECTOR) {
  const el = document.querySelector(selector)
  if (!el || el.dataset.vApp === 'true') {
    return appInstance
  }
  appInstance = createVueApp()
  appInstance.mount(selector)
  el.dataset.vApp = 'true'
  return appInstance
}

export function destroy(selector = SELECTOR) {
  const el = document.querySelector(selector)
  if (appInstance && el) {
    appInstance.unmount()
    appInstance = null
    el.dataset.vApp = 'false'
  }
}

function configReady() {
  return Boolean(getConfig().connectorUrl)
}

function bootstrapApp() {
  if (!document.querySelector(SELECTOR)) {
    return
  }
  if (!configReady()) {
    return
  }
  init(SELECTOR)
}

function scheduleBootstrap() {
  if (typeof Ext !== 'undefined' && typeof Ext.onReady === 'function') {
    Ext.onReady(bootstrapApp)
    return
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapApp, { once: true })
    return
  }
  bootstrapApp()
}

window.imageoptimizerAdmin = { init, destroy, bootstrap: bootstrapApp }
scheduleBootstrap()
