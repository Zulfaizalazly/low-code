<div class="flex items-start gap-3 p-4 rounded-xl border {{ ($field->config['variant'] ?? 'info') === 'warning' ? 'bg-green-50 border-green-200 text-green-800' : (($field->config['variant'] ?? 'info') === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-blue-50 border-blue-200 text-blue-800') }}">
    <p class="text-sm">{{ $field->config['message'] ?? $field->placeholder ?? $field->label }}</p>
</div>
