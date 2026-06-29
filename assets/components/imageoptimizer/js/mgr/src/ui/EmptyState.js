import { defineComponent } from 'vue'
import { Message } from 'primevue'

export default defineComponent({
  name: 'EmptyState',
  components: { Message },
  props: {
    title: { type: String, default: '' },
    detail: { type: String, default: '' },
    icon: { type: String, default: 'pi pi-inbox' },
  },
  template: `
    <div class="imageoptimizer-empty-state text-center py-5 px-3">
      <i :class="icon" class="text-4xl text-color-secondary mb-3 block" aria-hidden="true" />
      <Message v-if="title" severity="secondary" variant="simple" class="justify-content-center mb-2">
        {{ title }}
      </Message>
      <p v-if="detail" class="text-sm text-color-secondary m-0">{{ detail }}</p>
      <div v-if="$slots.default" class="mt-3">
        <slot />
      </div>
    </div>
  `,
})
