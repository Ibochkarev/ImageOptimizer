import { defineComponent, computed } from 'vue'
import {
  Toast,
  ConfirmDialog,
  Tabs,
  TabList,
  Tab,
  TabPanels,
  TabPanel,
} from 'primevue'
import Dashboard from './components/Dashboard.js'
import QueueGrid from './components/QueueGrid.js'
import SettingsForm from './components/SettingsForm.js'
import ServerCheck from './components/ServerCheck.js'
import CompatibilityTab from './components/CompatibilityTab.js'
import { getConfig, lex } from './request.js'

export default defineComponent({
  name: 'ImageOptimizerApp',
  components: {
    Toast,
    ConfirmDialog,
    Tabs,
    TabList,
    Tab,
    TabPanels,
    TabPanel,
    Dashboard,
    QueueGrid,
    SettingsForm,
    ServerCheck,
    CompatibilityTab,
  },
  setup() {
    const canSettings = computed(() => Number(getConfig().permissions?.settings) === 1)
    return { lex, canSettings }
  },
  template: `
    <Toast />
    <ConfirmDialog />
    <div class="imageoptimizer-admin">
      <div class="imageoptimizer-admin__panel">
        <Tabs value="dashboard" class="imageoptimizer-mgr-tabs">
          <TabList>
            <Tab value="dashboard">
              <i class="pi pi-chart-bar mr-2" aria-hidden="true" />{{ lex('imageoptimizer.tab.dashboard') }}
            </Tab>
            <Tab value="queue">
              <i class="pi pi-list mr-2" aria-hidden="true" />{{ lex('imageoptimizer.tab.queue') }}
            </Tab>
            <Tab value="settings" v-if="canSettings">
              <i class="pi pi-cog mr-2" aria-hidden="true" />{{ lex('imageoptimizer.tab.settings') }}
            </Tab>
            <Tab value="server">
              <i class="pi pi-server mr-2" aria-hidden="true" />{{ lex('imageoptimizer.tab.server') }}
            </Tab>
            <Tab value="compatibility">
              <i class="pi pi-link mr-2" aria-hidden="true" />{{ lex('imageoptimizer.tab.compatibility') }}
            </Tab>
          </TabList>
          <TabPanels>
            <TabPanel value="dashboard"><Dashboard /></TabPanel>
            <TabPanel value="queue"><QueueGrid /></TabPanel>
            <TabPanel value="settings" v-if="canSettings"><SettingsForm /></TabPanel>
            <TabPanel value="server"><ServerCheck /></TabPanel>
            <TabPanel value="compatibility"><CompatibilityTab /></TabPanel>
          </TabPanels>
        </Tabs>
      </div>
    </div>
  `,
})
