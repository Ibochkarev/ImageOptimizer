import { defineComponent } from 'vue'

export default defineComponent({
  name: 'PageSection',
  props: {
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
  },
  template: `
    <div class="imageoptimizer-page-section mb-3">
      <div class="flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div>
          <h3 v-if="title" class="imageoptimizer-page-section__title m-0">{{ title }}</h3>
          <p v-if="subtitle" class="imageoptimizer-page-section__subtitle m-0 mt-1 text-sm text-color-secondary">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap gap-2">
          <slot name="actions" />
        </div>
      </div>
      <slot />
    </div>
  `,
})
