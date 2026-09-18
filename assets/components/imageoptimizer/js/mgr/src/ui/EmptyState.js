import { defineComponent } from 'vue'

export default defineComponent({
  name: 'EmptyState',
  props: {
    title: { type: String, default: '' },
    detail: { type: String, default: '' },
    icon: { type: String, default: 'pi pi-inbox' },
  },
  template: `
    <div class="imageoptimizer-empty-state text-center py-4 px-3">
      <i :class="icon" class="text-3xl text-color-secondary mb-2 block" aria-hidden="true" />
      <p v-if="title" class="imageoptimizer-empty-state__title">{{ title }}</p>
      <p v-if="detail" class="imageoptimizer-empty-state__detail">{{ detail }}</p>
      <div v-if="$slots.default" class="mt-3">
        <slot />
      </div>
    </div>
  `,
})
