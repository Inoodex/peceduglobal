<template>
  <div
    class="flex flex-col w-full"
    :class="isAdmin ? 'items-end' : 'items-start'"
  >
    <div class="flex items-end gap-2 max-w-[80%]" :class="isAdmin ? 'flex-row-reverse' : 'flex-row'">
      <!-- Guest avatar (left side only) -->
      <div
        v-if="!isAdmin"
        class="w-7 h-7 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary text-[11px] dark:from-primary/30 dark:to-primary/20 flex-shrink-0 mb-1"
      >
        {{ avatarInitial }}
      </div>

      <!-- Bubble -->
      <div
        class="px-4 py-2.5 rounded-2xl shadow-sm text-sm break-words"
        :class="[
          isAdmin
            ? 'bg-primary text-white rounded-tr-md rounded-br-2xl rounded-tl-2xl rounded-bl-2xl'
            : 'bg-white dark:bg-[#161B22] border border-gray-100 dark:border-gray-800 text-gray-900 dark:text-white rounded-tl-md rounded-bl-2xl rounded-tr-2xl rounded-br-2xl'
        ]"
      >
        <p class="whitespace-pre-wrap leading-relaxed">{{ message.message }}</p>

        <!-- Footer: time + read receipt -->
        <div
          class="flex items-center gap-1 mt-1 -mb-0.5"
          :class="isAdmin ? 'justify-end' : 'justify-start'"
        >
          <span class="text-[9px]" :class="isAdmin ? 'text-white/70' : 'text-gray-400'">
            {{ formattedTime }}
          </span>

          <!-- Read receipts: only on admin (own) messages -->
          <span v-if="isAdmin" class="flex items-center">
            <!-- ✓✓ read -->
            <CheckCheck
              v-if="isRead"
              class="w-3 h-3 text-sky-200"
              :title="readTitle"
            />
            <!-- ✓ sent (not yet read) -->
            <Check
              v-else
              class="w-3 h-3 text-white/60"
              title="Sent"
            />
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Check, CheckCheck } from 'lucide-vue-next';

const props = defineProps({
  message: { type: Object, required: true },
  isAdmin: { type: Boolean, default: false },
});

// Read when either the boolean flag is set OR a read_at timestamp exists
// (read_at is added in a later migration; is_read is the current schema).
const isRead = computed(() => {
  return Boolean(props.message.is_read) || Boolean(props.message.read_at);
});

const readTitle = computed(() => {
  if (props.message.read_at) {
    const d = new Date(props.message.read_at);
    return `Read ${d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
  }
  return 'Read';
});

const avatarInitial = computed(() => {
  const email = props.message.sender_email || props.message.guest_email;
  return email ? email.charAt(0).toUpperCase() : 'G';
});

const formattedTime = computed(() => {
  if (!props.message.created_at) return '';
  const date = new Date(props.message.created_at);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});
</script>
