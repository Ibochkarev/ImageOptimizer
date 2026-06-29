import { defineComponent, computed, onMounted, ref } from 'vue'
import {
  Tabs,
  TabList,
  Tab,
  TabPanels,
  TabPanel,
  ToggleSwitch,
  InputText,
  InputNumber,
  Textarea,
  Button,
  Skeleton,
} from 'primevue'
import { useImageOptimizerApi } from '../composables/useImageOptimizerApi.js'
import { useImageOptimizerNotify } from '../composables/useImageOptimizerNotify.js'
import PageSection from '../ui/PageSection.js'
import { getConfig, lex } from '../request.js'

export default defineComponent({
  name: 'SettingsForm',
  components: {
    Tabs, TabList, Tab, TabPanels, TabPanel,
    ToggleSwitch, InputText, InputNumber, Textarea, Button, Skeleton, PageSection,
  },
  setup() {
    const api = useImageOptimizerApi()
    const { notifySuccess, notifyError } = useImageOptimizerNotify()
    const loading = ref(false)
    const saving = ref(false)
    const settings = ref({})
    const canEdit = computed(() => Number(getConfig().permissions?.settings) === 1)

    async function loadSettings() {
      loading.value = true
      try {
        const res = await api.settingsGet()
        settings.value = { ...(res.data || {}) }
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    async function saveSettings() {
      if (!canEdit.value) {
        return
      }
      saving.value = true
      try {
        await api.settingsUpdate(settings.value)
        notifySuccess(lex('imageoptimizer.settings.saved'))
        await loadSettings()
      } catch (e) {
        notifyError(e.message)
      } finally {
        saving.value = false
      }
    }

    function bool(key) {
      return !!settings.value[key]
    }

    function setBool(key, value) {
      settings.value[key] = value
    }

    onMounted(loadSettings)

    return {
      lex,
      loading,
      saving,
      settings,
      canEdit,
      saveSettings,
      bool,
      setBool,
    }
  },
  template: `
    <div class="imageoptimizer-tab-panel">
      <PageSection :title="lex('imageoptimizer.tab.settings')">
        <template #actions>
          <Button v-if="canEdit" :label="lex('imageoptimizer.save')" icon="pi pi-check"
            :loading="saving" :disabled="loading" @click="saveSettings" />
        </template>
        <Skeleton v-if="loading" height="12rem" class="mb-3" />
        <Tabs v-else value="general">
          <TabList>
            <Tab value="general">{{ lex('imageoptimizer.settings.tab.general') }}</Tab>
            <Tab value="formats">{{ lex('imageoptimizer.settings.tab.formats') }}</Tab>
            <Tab value="frontend">{{ lex('imageoptimizer.settings.tab.frontend') }}</Tab>
            <Tab value="processing">{{ lex('imageoptimizer.settings.tab.processing') }}</Tab>
          </TabList>
          <TabPanels>
            <TabPanel value="general">
              <div class="grid mt-2">
                <div class="col-12 flex align-items-center gap-3">
                  <ToggleSwitch :modelValue="bool('enabled')" :disabled="!canEdit"
                    @update:modelValue="setBool('enabled', $event)" />
                  <span>{{ lex('setting_imageoptimizer_enabled') }}</span>
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_cron_limit') }}</label>
                  <InputNumber v-model="settings.cron_limit" class="w-full" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_retention_days') }}</label>
                  <InputNumber v-model="settings.retention_days" class="w-full" :disabled="!canEdit" />
                </div>
              </div>
            </TabPanel>
            <TabPanel value="formats">
              <div class="grid mt-2">
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_formats') }}</label>
                  <InputText v-model="settings.formats" class="w-full" :disabled="!canEdit" />
                </div>
                <div class="col-12 flex align-items-center gap-3">
                  <ToggleSwitch :modelValue="bool('avif_enabled')" :disabled="!canEdit"
                    @update:modelValue="setBool('avif_enabled', $event)" />
                  <span>{{ lex('setting_imageoptimizer_avif_enabled') }}</span>
                </div>
                <div class="col-12 md:col-4 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_quality_jpeg') }}</label>
                  <InputNumber v-model="settings.quality_jpeg" class="w-full" :min="1" :max="100" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-4 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_quality_png') }}</label>
                  <InputNumber v-model="settings.quality_png" class="w-full" :min="1" :max="100" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_method_priority') }}</label>
                  <InputText v-model="settings.method_priority" class="w-full" :disabled="!canEdit" />
                </div>
              </div>
            </TabPanel>
            <TabPanel value="frontend">
              <div class="grid mt-2">
                <div class="col-12 flex align-items-center gap-3">
                  <ToggleSwitch :modelValue="bool('inject_frontend')" :disabled="!canEdit"
                    @update:modelValue="setBool('inject_frontend', $event)" />
                  <span>{{ lex('setting_imageoptimizer_inject_frontend') }}</span>
                </div>
                <div class="col-12 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_default_sizes') }}</label>
                  <Textarea v-model="settings.default_sizes" class="w-full" rows="2" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_skip_src_pattern') }}</label>
                  <InputText v-model="settings.skip_src_pattern" class="w-full" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_skip_classes') }}</label>
                  <InputText v-model="settings.skip_classes" class="w-full" :disabled="!canEdit" />
                </div>
              </div>
            </TabPanel>
            <TabPanel value="processing">
              <div class="grid mt-2">
                <div class="col-12 flex align-items-center gap-3">
                  <ToggleSwitch :modelValue="bool('convert_on_upload')" :disabled="!canEdit"
                    @update:modelValue="setBool('convert_on_upload', $event)" />
                  <span>{{ lex('setting_imageoptimizer_convert_on_upload') }}</span>
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_breakpoints') }}</label>
                  <InputText v-model="settings.breakpoints" class="w-full" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_variant_pattern') }}</label>
                  <InputText v-model="settings.variant_pattern" class="w-full" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_stuck_minutes') }}</label>
                  <InputNumber v-model="settings.stuck_minutes" class="w-full" :disabled="!canEdit" />
                </div>
                <div class="col-12 md:col-6 field">
                  <label class="block font-medium mb-1">{{ lex('setting_imageoptimizer_max_memory_limit') }}</label>
                  <InputText v-model="settings.max_memory_limit" class="w-full" :disabled="!canEdit" />
                </div>
              </div>
            </TabPanel>
          </TabPanels>
        </Tabs>
      </PageSection>
    </div>
  `,
})
