<x-filament-panels::page>
    <x-filament-panels::form wire:submit="run">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
