<div wire:key="children-{{ $person->id }}" class="min-w-xs flex flex-col rounded-sm bg-white shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] dark:bg-neutral-700 text-neutral-800 dark:text-neutral-50">
    <div class="flex flex-col p-2 text-lg font-medium border-b-2 rounded-t h-14 min-h-min border-neutral-100 dark:border-neutral-600 dark:text-neutral-50">
        <div class="flex flex-wrap items-start justify-center gap-2">
            <div class="flex-1 grow max-w-full min-w-max">
                {{ $editMode ? __('person.edit_children') : __('person.children') }}
                @if (!$editMode && count($person->couples) > 0)
                    <x-ts-badge color="emerald" sm text="{{ count($children) }}" />
                @endif
            </div>

            <div class="flex gap-2 items-center">
                @if ($editMode)
                    {{-- Przyciski w trybie edycji --}}
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
                    @endif

                    @if (auth()->user()->hasPermission('person:create'))
                        <x-ts-dropdown icon="tabler.dots-vertical" position="bottom-end">
                            <a href="/people/{{ $person->id }}/add-child">
                                <x-ts-dropdown.items>
                                    <x-ts-icon icon="tabler.user-plus" class="inline-block size-5 mr-2" />
                                    {{ __('person.add_child') }}
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

            {{-- Lista dzieci z przyciskami odłączania --}}
            @if (count($children) > 0)
                <div class="mb-4">
                    <h4 class="font-medium mb-2">{{ __('person.current_children') }}:</h4>
                    @foreach ($children as $child)
                        @if (!isset($child->type))
                            <div class="flex items-center justify-between p-2 mb-2 bg-white dark:bg-neutral-700 rounded border">
                                <div>
                                    <span @class(['text-red-600 dark:text-red-400' => $child->isDeceased()])>
                                        {{ $child->name }}
                                    </span>
                                    <x-ts-icon icon="tabler.{{ $child->sex === 'm' ? 'gender-male' : 'gender-female' }}" class="inline-block size-5" />
                                </div>
                                <x-ts-button wire:click="disconnect({{ $child->id }})" color="red" class="text-xs">
                                    <x-ts-icon icon="tabler.plug-connected-x" class="inline-block size-4" />
                                    {{ __('app.disconnect') }}
                                </x-ts-button>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Formularz dodawania dzieci --}}
            <div class="mt-4">
                <h4 class="font-medium mb-2">{{ __('person.add_existing_person_as_child') }}:</h4>
                
                @if ($this->availablePersons->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        {{ __('app.nothing_available') }}
                    </p>
                    <a href="/people/{{ $person->id }}/add-child">
                        <x-ts-button color="primary" class="text-sm">
                            <x-ts-icon icon="tabler.user-plus" class="inline-block size-4" />
                            {{ __('person.add_new_person_as_child') }}
                        </x-ts-button>
                    </a>
                @else
                    <div class="space-y-2">
                        <x-ts-select.styled wire:model="selected_persons" id="selected_persons" :options="$this->availablePersons"
                            select="label:name|value:id" placeholder="{{ __('app.select') }} ..." searchable multiple />
                        
                        {{-- DEBUG: selected_persons = {{ json_encode($selected_persons) }} --}}
                        
                        <x-ts-button wire:click="addChildren" color="primary" class="w-full">
                            <x-ts-icon icon="tabler.plus" class="inline-block size-4" />
                            @if (count($selected_persons) > 0)
                                {{ __('app.add') }} ({{ count($selected_persons) }})
                            @else
                                {{ __('app.add') }}
                            @endif
                        </x-ts-button>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- View Mode --}}
        @if (count($children) > 0)
            @foreach ($children as $child)
                <div class="p-2 flex flex-wrap gap-2 justify-center items-start @if (!$loop->last) border-b @endif">
                    <div class="flex-1 grow max-w-full min-w-max">
                        <x-link href="/people/{{ $child->id }}" @class(['text-red-600 dark:text-red-400' => $child->isDeceased()])>
                            {{ $child->name }}
                        </x-link>

                        <x-ts-icon icon="tabler.{{ $child->sex === 'm' ? 'gender-male' : 'gender-female' }}" class="inline-block size-5" />
                        @if (isset($child->type))
                            <x-ts-icon icon="tabler.heart-plus" class="inline-block size-5 text-emerald-600" />
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <p class="p-2">{{ __('app.nothing_recorded') }}</p>
        @endif
    @endif
</div>
