<button type="button"
        class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-all active:scale-95">
    {{ $field->config['text'] ?? $field->placeholder ?? $field->label }}
</button>
