import { defineComponent, onMounted, ref } from 'vue'
import { Card, Tag, Button, ProgressBar, InputText, Message } from 'primevue'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import { lex } from '../request.js'

const ENCODER_LABELS = {
  cwebp: 'cwebp',
  avifenc: 'avifenc',
  gd_webp: 'GD WebP',
  gd_avif: 'GD AVIF',
  imagick_webp: 'Imagick WebP',
  imagick_heic: 'Imagick HEIC',
  heif_convert: 'heif-convert',
}

export default defineComponent({
  name: 'ServerCheck',
  components: { Card, Tag, Button, ProgressBar, InputText, Message },
  setup() {
    const api = useImageOptimizerApi()
    const { notifyError, notifySuccess } = useImageOptimizerNotify()
    const loading = ref(false)
    const data = ref({ encoders: {}, readiness: 0, php_version: '', memory_limit: '' })
    const cronCmd = ref('')

    async function load() {
      loading.value = true
      try {
        const res = await api.serverCheck()
        data.value = res.data || {}
        const root = typeof MODX_BASE_PATH !== 'undefined' ? MODX_BASE_PATH : ''
        cronCmd.value = `*/10 * * * * php ${root}core/components/imageoptimizer/cron/convert.php`
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    function copyCron() {
      if (!cronCmd.value) {
        return
      }
      navigator.clipboard?.writeText(cronCmd.value)
      notifySuccess(lex('imageoptimizer.server.cron_copied'))
    }

    onMounted(load)

    return {
      lex,
      loading,
      data,
      cronCmd,
      load,
      copyCron,
      encoderLabels: ENCODER_LABELS,
    }
  },
  template: `
    <div class="imageoptimizer-tab-panel">
      <div class="flex align-items-center justify-content-between mb-3">
        <div class="font-semibold text-lg">{{ lex('imageoptimizer.server.title') }}</div>
        <Button icon="pi pi-refresh" :loading="loading" text rounded @click="load" />
      </div>
      <ProgressBar :value="data.readiness || 0" class="mb-4" />
      <div class="grid">
        <div v-for="(available, key) in data.encoders" :key="key" class="col-12 md:col-4">
          <Card class="imageoptimizer-compat-card h-full">
            <template #content>
              <div class="imageoptimizer-compat-card__body">
                <div class="imageoptimizer-compat-card__title">{{ encoderLabels[key] || key }}</div>
                <Tag
                  :severity="available ? 'success' : 'danger'"
                  :value="available ? lex('imageoptimizer.server.available') : lex('imageoptimizer.server.missing')"
                  rounded />
              </div>
            </template>
          </Card>
        </div>
      </div>
      <Message severity="secondary" class="mt-3 w-full">
        PHP {{ data.php_version }} · memory {{ data.memory_limit }}
      </Message>
      <Card class="mt-3">
        <template #title>{{ lex('imageoptimizer.server.cron') }}</template>
        <template #content>
          <div class="flex gap-2 align-items-center">
            <InputText v-model="cronCmd" class="flex-1" readonly />
            <Button icon="pi pi-copy" @click="copyCron" />
          </div>
        </template>
      </Card>
    </div>
  `,
})
