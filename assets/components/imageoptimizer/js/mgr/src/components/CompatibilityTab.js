import { defineComponent, onMounted, ref } from 'vue'
import { Card, Tag } from 'primevue'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import { lex } from '../request.js'

const PACKAGES = [
  { key: 'thumb3x', labelKey: 'imageoptimizer.compat.thumb3x', hintKey: 'imageoptimizer.compat.hint.thumb3x' },
  { key: 'pthumb', labelKey: 'imageoptimizer.compat.pthumb', hintKey: 'imageoptimizer.compat.hint.pthumb' },
  { key: 'minishop3', labelKey: 'imageoptimizer.compat.minishop3', hintKey: 'imageoptimizer.compat.hint.minishop3' },
  { key: 'vuetools', labelKey: 'imageoptimizer.compat.vuetools', hintKey: 'imageoptimizer.compat.hint.vuetools' },
]

export default defineComponent({
  name: 'CompatibilityTab',
  components: { Card, Tag },
  setup() {
    const api = useImageOptimizerApi()
    const { notifyError } = useImageOptimizerNotify()
    const loading = ref(false)
    const installed = ref({})

    async function load() {
      loading.value = true
      try {
        const res = await api.compatibilityList()
        installed.value = res.data || {}
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    onMounted(load)

    return { lex, loading, installed, packages: PACKAGES }
  },
  template: `
    <div class="imageoptimizer-tab-panel">
      <div class="font-semibold text-lg mb-3">{{ lex('imageoptimizer.compat.title') }}</div>
      <div class="grid">
        <div v-for="pkg in packages" :key="pkg.key" class="col-12 md:col-6">
          <Card class="imageoptimizer-compat-card h-full">
            <template #content>
              <div class="imageoptimizer-compat-card__body">
                <div class="imageoptimizer-compat-card__title">{{ lex(pkg.labelKey) }}</div>
                <Tag
                  :severity="installed[pkg.key] ? 'success' : 'secondary'"
                  :value="installed[pkg.key] ? lex('imageoptimizer.compat.installed') : lex('imageoptimizer.compat.not_installed')"
                  rounded />
                <p v-if="pkg.hintKey" class="imageoptimizer-compat-card__hint">{{ lex(pkg.hintKey) }}</p>
              </div>
            </template>
          </Card>
        </div>
      </div>
    </div>
  `,
})
