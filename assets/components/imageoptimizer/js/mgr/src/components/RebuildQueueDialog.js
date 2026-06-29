import { defineComponent, ref, watch } from 'vue'
import { Dialog, Button, InputNumber, InputText, Checkbox } from 'primevue'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import { IO_DIALOG_PT, IO_DIALOG_CONTENT_STYLE, IO_DIALOG_STYLE } from '../dialogUi.js'
import { lex, lexFormat } from '../request.js'

export default defineComponent({
  name: 'RebuildQueueDialog',
  components: { Dialog, Button, InputNumber, InputText, Checkbox },
  props: {
    visible: { type: Boolean, default: false },
  },
  emits: ['update:visible', 'done'],
  setup(props, { emit }) {
    const api = useImageOptimizerApi()
    const { notifyError, notifySuccess } = useImageOptimizerNotify()
    const sourceId = ref(1)
    const path = ref('')
    const dryRun = ref(false)
    const loading = ref(false)
    const previewCount = ref(null)

    function close() {
      emit('update:visible', false)
    }

    async function preview() {
      loading.value = true
      previewCount.value = null
      try {
        const res = await api.queueRebuild({
          source: sourceId.value,
          path: path.value,
          dry_run: dryRun.value ? 1 : 0,
        })
        previewCount.value = Number(res.data?.enqueued ?? 0)
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    async function run() {
      loading.value = true
      try {
        const res = await api.queueRebuild({
          source: sourceId.value,
          path: path.value,
          dry_run: 0,
        })
        notifySuccess(lexFormat('imageoptimizer.queue.rebuild_done', res.data?.enqueued ?? 0))
        emit('done')
        close()
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    watch(() => props.visible, (v) => {
      if (v) {
        previewCount.value = null
      }
    })

    return {
      lex,
      lexFormat,
      sourceId,
      path,
      dryRun,
      loading,
      previewCount,
      close,
      preview,
      run,
      dialogPt: IO_DIALOG_PT,
      dialogStyle: IO_DIALOG_STYLE,
      dialogContentStyle: IO_DIALOG_CONTENT_STYLE,
    }
  },
  template: `
    <Dialog
      :visible="visible"
      modal
      :header="lex('imageoptimizer.queue.rebuild')"
      class="imageoptimizer-dialog"
      :style="dialogStyle"
      :pt="dialogPt"
      :contentStyle="dialogContentStyle"
      @update:visible="(v) => !v && close()">
      <div class="flex flex-column gap-3">
        <div class="field">
          <label class="block font-medium mb-1">{{ lex('imageoptimizer.col.source') }}</label>
          <InputNumber v-model="sourceId" class="w-full" :min="1" />
        </div>
        <div class="field">
          <label class="block font-medium mb-1">{{ lex('imageoptimizer.col.path') }}</label>
          <InputText v-model="path" class="w-full" :placeholder="lex('imageoptimizer.queue.path_optional')" />
        </div>
        <div class="flex align-items-center gap-2">
          <Checkbox v-model="dryRun" inputId="io-rebuild-dry" binary />
          <label for="io-rebuild-dry">{{ lex('imageoptimizer.dry_run') }}</label>
        </div>
        <p v-if="previewCount !== null" class="text-sm m-0">
          {{ lexFormat('imageoptimizer.queue.preview_count', previewCount) }}
        </p>
      </div>
      <template #footer>
        <Button :label="lex('imageoptimizer.preview')" icon="pi pi-eye" severity="secondary" :loading="loading" @click="preview" />
        <Button :label="lex('imageoptimizer.run')" icon="pi pi-play" :loading="loading" @click="run" />
      </template>
    </Dialog>
  `,
})
