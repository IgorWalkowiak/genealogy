<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-800 dark:text-neutral-200">
            {{ __('person.edit') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl dark:bg-neutral-800 sm:rounded-lg">
                <livewire:people.edit.unified-edit :person="$person" />
            </div>
        </div>
    </div>
</x-app-layout>

