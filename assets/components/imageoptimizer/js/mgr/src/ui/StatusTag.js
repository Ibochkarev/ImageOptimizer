import { defineComponent, computed } from 'vue'
import { Tag } from 'primevue'
import { lex } from '../request.js'

const STATUS_SEVERITY = {
  pending: 'warn',
  processing: 'info',
  done: 'success',
  failed: 'danger',
  skipped: 'secondary',
}

export default defineComponent({
  name: 'QueueStatusTag',
  components: { Tag },
  props: {
    status: { type: String, default: '' },
  },
  setup(props) {
    const tag = computed(() => {
      const key = props.status || 'pending'
      return {
        severity: STATUS_SEVERITY[key] || 'secondary',
        label: lex(`imageoptimizer.status.${key}`) || key,
      }
    })
    return { tag }
  },
  template: `
    <Tag :severity="tag.severity" :value="tag.label" rounded />
  `,
})
