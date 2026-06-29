import { defineComponent } from 'vue'
import { Card, Skeleton } from 'primevue'

export default defineComponent({
  name: 'StatCard',
  components: { Card, Skeleton },
  props: {
    label: { type: String, default: '' },
    value: { type: [String, Number], default: '—' },
    icon: { type: String, default: '' },
    loading: { type: Boolean, default: false },
  },
  template: `
    <Card class="imageoptimizer-stat-card h-full">
      <template #content>
        <div class="flex align-items-start justify-content-between gap-2">
          <div class="flex-1 min-w-0">
            <div class="text-sm text-color-secondary mb-2">{{ label }}</div>
            <Skeleton v-if="loading" width="4rem" height="2rem" />
            <div v-else class="imageoptimizer-stat-card__value">{{ value }}</div>
            <div v-if="$slots.default" class="mt-2">
              <slot />
            </div>
          </div>
          <i v-if="icon" :class="icon" class="imageoptimizer-stat-card__icon" aria-hidden="true" />
        </div>
      </template>
    </Card>
  `,
})
