<div wire:key="family-{{ $person->id }}" class="min-w-xs flex flex-col rounded-sm bg-white shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] dark:bg-neutral-700 text-neutral-800 dark:text-neutral-50">
    <div class="flex flex-col p-2 text-lg font-medium border-b-2 rounded-t h-14 min-h-min border-neutral-100 dark:border-neutral-600 dark:text-neutral-50">
        <div class="flex flex-wrap items-start justify-center gap-2">
            <div class="items-center justify-center flex-1 grow max-w-full align-middle min-w-max">
                {{ $editMode ? __('person.edit_family') : __('person.family') }}
            </div>

            <div class="flex gap-2 items-center">
                @if ($editMode)
                    {{-- Przyciski w trybie edycji --}}
                    <x-ts-button wire:click="saveFamily" color="primary" class="text-xs py-1">
                        <x-ts-icon icon="tabler.device-floppy" class="inline-block size-4" />
                        <span class="hidden sm:inline ml-1">{{ __('app.save') }}</span>
                    </x-ts-button>
                    <x-ts-button wire:click="cancelEdit" color="secondary" class="text-xs py-1">
                        <x-ts-icon icon="tabler.x" class="inline-block size-4" />
                        <span class="hidden sm:inline ml-1">{{ __('app.cancel') }}</span>
                    </x-ts-button>
                @else
                    {{-- Przyciski w trybie wyświetlania --}}
                    @if (auth()->user()->hasPermission('person:update'))
                        <x-ts-button wire:click="enableEditMode" color="primary" class="text-xs py-1">
                            <x-ts-icon icon="tabler.pencil" class="inline-block size-4" />
                            <span class="hidden sm:inline ml-1">{{ __('app.edit') }}</span>
                        </x-ts-button>

                        <x-ts-dropdown icon="tabler.dots-vertical" position="bottom-end">
                            @if ((!isset($person->father_id) or !isset($person->mother_id)) and !isset($person->parents_id))
                                @if (!isset($person->father_id))
                                    <a href="/people/{{ $person->id }}/add-father">
                                        <x-ts-dropdown.items>
                                            <x-ts-icon icon="tabler.user-plus" class="inline-block size-5 mr-2" />
                                            {{ __('person.add_father') }}
                                        </x-ts-dropdown.items>
                                    </a>
                                @endif

                                @if (!isset($person->mother_id))
                                    <a href="/people/{{ $person->id }}/add-mother">
                                        <x-ts-dropdown.items>
                                            <x-ts-icon icon="tabler.user-plus" class="inline-block size-5 mr-2" />
                                            {{ __('person.add_mother') }}
                                        </x-ts-dropdown.items>
                                    </a>
                                @endif

                                <hr />
                            @endif

                            <a href="/people/{{ $person->id }}/edit-family">
                                <x-ts-dropdown.items>
                                    <x-ts-icon icon="tabler.edit" class="inline-block size-5 mr-2" />
                                    {{ __('person.edit_family') }}
                                </x-ts-dropdown.items>
                            </a>
                        </x-ts-dropdown>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if ($editMode)
        {{-- Edit Mode --}}
        <div class="p-4 bg-neutral-50 dark:bg-neutral-800">
            <x-ts-errors class="mb-4" close />

            <div class="grid grid-cols-1 gap-4">
                {{-- father_id --}}
                <div>
                    <x-ts-select.styled wire:model="father_id" id="father_id" label="{{ __('person.father') }} ({{ __('person.biological') }}) :" :options="$fathers"
                        select="label:name|value:id" placeholder="{{ __('app.select') }} ..." searchable />
                </div>

                {{-- mother_id --}}
                <div>
                    <x-ts-select.styled wire:model="mother_id" id="mother_id" label="{{ __('person.mother') }} ({{ __('person.biological') }}) :" :options="$mothers"
                        select="label:name|value:id" placeholder="{{ __('app.select') }} ..." searchable />
                </div>
            </div>
        </div>
    @else
        {{-- View Mode --}}
        <div class="grid grid-cols-6">
            <div class="col-span-2 py-2 pl-2 border-b">{{ __('person.father') }}</div>
            <div class="col-span-4 p-2 border-b">
                @if ($person->father)
                    <x-link href="/people/{{ $person->father->id }}" @class(['text-red-600 dark:text-red-400' => $person->father->isDeceased()])>
                        {{ $person->father->name }}
                    </x-link>
                    <x-ts-icon icon="tabler.{{ $person->father->sex === 'm' ? 'gender-male' : 'gender-female' }}" class="inline-block size-5" />
                @endif
            </div>

            <div class="col-span-2 py-2 pl-2 border-b">{{ __('person.mother') }}</div>
            <div class="col-span-4 p-2 border-b">
                @if ($person->mother)
                    <x-link href="/people/{{ $person->mother->id }}" @class(['text-red-600 dark:text-red-400' => $person->mother->isDeceased()])>
                        {{ $person->mother->name }}
                    </x-link>
                    <x-ts-icon icon="tabler.{{ $person->mother->sex === 'm' ? 'gender-male' : 'gender-female' }}" class="inline-block size-5" />
                @endif
            </div>

            <div class="col-span-2 py-2 pl-2">{{ __('person.partner') }}</div>
            <div class="col-span-4 p-2">
                @if ($person->currentPartner())
                    <x-link href="/people/{{ $person->currentPartner()->id }}" @class(['text-red-600 dark:text-red-400' => $person->currentPartner()->isDeceased()])>
                        {{ $person->currentPartner()->name }}
                    </x-link>
                    <x-ts-icon icon="tabler.{{ $person->currentPartner()->sex === 'm' ? 'gender-male' : 'gender-female' }}" class="inline-block size-5" />
                @endif
            </div>
        </div>
    @endif
</div>
