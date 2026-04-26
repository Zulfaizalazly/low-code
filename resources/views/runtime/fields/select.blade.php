<select id="{{ $field->field_key }}"
        wire:model.defer="formData.{{ $field->field_key }}"
        class="block w-full px-4 py-3 rounded-[14px] border border-black/[0.08] bg-[#f9f9fb] focus:bg-white focus:border-amber-400 focus:ring-4 focus:ring-amber-500/10 transition-all text-[15px] text-[#1d1d1f] appearance-none"
        style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2386868b%22 stroke-width=%222%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19 9l-7 7-7-7%22/></svg>'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;">
    <option value="">Select...</option>
    @foreach(($field->config['options'] ?? []) as $val => $label)
        <option value="{{ $val }}">{{ $label }}</option>
    @endforeach
</select>
