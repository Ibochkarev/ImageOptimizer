import { defineComponent, computed } from 'vue'
import { Card, Skeleton } from 'primevue'

export default defineComponent({
  name: 'StatCard',
  components: { Card, Skeleton },
  props: {
    label: { type: String, default: '' },
    value: { type: [String, Number], default: '—' },
    icon: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    /** default | warn | success | danger | muted */
    tone: { type: String, default: 'default' },
    /** Smaller / de-emphasized card (e.g. readiness) */
    secondary: { type: Boolean, default: false },
  },
  setup(props) {
    const cardClass = computed(() => {
      const classes = ['imageoptimizer-stat-card', 'h-full']
      if (props.tone !== 'default') {
        classes.push(`imageoptimizer-stat-card--${props.tone}`)
      }
      if (props.secondary) {
        classes.push('imageoptimizer-stat-card--secondary')
      }
      return classes.join(' ')
    })
    return { cardClass }
  },
  template: `
    <Card :class="cardClass">
      <template #content>
        <div class="flex align-items-start justify-content-between gap-2">
          <div class="flex-1 min-w-0">
            <div class="text-sm text-color-secondary mb-1">{{ label }}</div>
            <Skeleton v-if="loading" width="4rem" height="1.75rem" />
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
