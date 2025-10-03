<div wire:key="heading-{{ $person->id }}" class="p-2 pb-0 rounded-sm dark:text-neutral-200 bg-white dark:bg-neutral-700 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)]">
    <!-- Mobile layout (stacked) -->
    <div class="md:hidden">
        <!-- Name section -->
        <div class="mb-2">
            <div class="text-lg font-medium">
                <div>{{ $person->name }}</div>
                @if (auth()->user()->is_developer)
                    <div class="text-cyan-500 text-sm">{{ $person->team->name }}</div>
                @endif
            </div>
        </div>

        <!-- Buttons section -->
        <div class="print:hidden">
            {{-- DEBUG: Route = {{ request()->route()->getName() }} | Permission = {{ auth()->user()->hasPermission('person:update') ? 'YES' : 'NO' }} --}}
            
            <div class="flex flex-wrap gap-1">
                @if (request()->routeIs('people.show'))
                    @if ($globalEditMode)
                        <x-ts-button wire:click="saveGlobalEdit" color="primary" class="mb-2 text-xs" title="{{ __('app.save') }}">
                            <x-ts-icon icon="tabler.device-floppy" class="inline-block size-4" />
                        </x-ts-button>

                        <x-ts-button wire:click="cancelGlobalEdit" color="secondary" class="mb-2 text-xs" title="{{ __('app.cancel') }}">
                            <x-ts-icon icon="tabler.x" class="inline-block size-4" />
                        </x-ts-button>
                    @else
                        @if (auth()->user()->hasPermission('person:update'))
                            <x-ts-button wire:click="enableGlobalEdit" color="primary" class="mb-2 text-xs" title="{{ __('app.edit') }}">
                                <x-ts-icon icon="tabler.pencil" class="inline-block size-4" />
                            </x-ts-button>
                        @endif
                    @endif
                @endif

                <x-ts-button href="/people/{{ $person->id }}" color="{{ request()->routeIs('people.show') ? 'yellow' : 'primary' }}" class="mb-2 text-xs" title="{{ __('person.profile') }}">
                    <x-ts-icon icon="tabler.id" class="inline-block size-4" />
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/ancestors" color="{{ request()->routeIs('people.ancestors') ? 'yellow' : 'secondary' }}" class="mb-2 text-xs" title="{{ __('person.ancestors') }}">
                    <x-ts-icon icon="tabler.binary-tree" class="inline-block size-4 rotate-180" />
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/descendants" color="{{ request()->routeIs('people.descendants') ? 'yellow' : 'secondary' }}" class="mb-2 text-xs" title="{{ __('person.descendants') }}">
                    <x-ts-icon icon="tabler.binary-tree" class="inline-block size-4" />
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/chart" color="{{ request()->routeIs('people.chart') ? 'yellow' : 'secondary' }}" class="mb-2 text-xs" title="{{ __('app.family_chart') }}">
                    <x-ts-icon icon="tabler.social" class="inline-block size-4" />
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/history" color="{{ request()->routeIs('people.history') ? 'yellow' : 'cyan' }}" class="mb-2 text-xs" title="{{ __('app.history') }}">
                    <x-ts-icon icon="tabler.history" class="inline-block size-4" />
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/datasheet" color="{{ request()->routeIs('people.datasheet') ? 'yellow' : 'secondary' }}" class="mb-2 text-xs" title="{{ __('app.datasheet') }}">
                    <x-ts-icon icon="tabler.pdf" class="inline-block size-4" />
                </x-ts-button>
            </div>
        </div>
    </div>

    <!-- Desktop layout (original side-by-side) - md and up -->
    <div class="hidden md:block">
        <div class="flex flex-wrap">
            <div class="flex-1 grow max-w-full text-lg font-medium">
                <div>{{ $person->name }}</div>

                @if (auth()->user()->is_developer)
                    <div class="text-cyan-500">{{ $person->team->name }}</div>
                @endif
            </div>

            <div class="flex-1 grow max-w-full print:hidden text-end">
                {{-- DEBUG: Route = {{ request()->route()->getName() }} --}}
                {{-- DEBUG: Has Permission = {{ auth()->user()->hasPermission('person:update') ? 'YES' : 'NO' }} --}}
                
                @if (request()->routeIs('people.show'))
                    @if ($globalEditMode)
                        <x-ts-button wire:click="saveGlobalEdit" color="primary" class="mb-3 mr-2 text-sm">
                            <x-ts-icon icon="tabler.device-floppy" class="inline-block size-5" />
                            {{ __('app.save') }}
                        </x-ts-button>

                        <x-ts-button wire:click="cancelGlobalEdit" color="secondary" class="mb-3 mr-2 text-sm">
                            <x-ts-icon icon="tabler.x" class="inline-block size-5" />
                            {{ __('app.cancel') }}
                        </x-ts-button>
                    @else
                        @if (auth()->user()->hasPermission('person:update'))
                            <x-ts-button wire:click="enableGlobalEdit" color="primary" class="mb-3 mr-2 text-sm">
                                <x-ts-icon icon="tabler.pencil" class="inline-block size-5" />
                                {{ __('app.edit') }}
                            </x-ts-button>
                        @else
                            {{-- DEBUG: No permission for person:update --}}
                        @endif
                    @endif
                @else
                    {{-- DEBUG: Not on people.show route --}}
                @endif

                <x-ts-button href="/people/{{ $person->id }}" color="{{ request()->routeIs('people.show') ? 'yellow' : 'primary' }}" class="mb-3 mr-2 text-sm">
                    <x-ts-icon icon="tabler.id" class="inline-block size-5" />
                    {{ __('person.profile') }}
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/ancestors" color="{{ request()->routeIs('people.ancestors') ? 'yellow' : 'secondary' }}" class="mb-3 mr-2 text-sm">
                    <x-ts-icon icon="tabler.binary-tree" class="inline-block size-5 rotate-180" />
                    {{ __('person.ancestors') }}
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/descendants" color="{{ request()->routeIs('people.descendants') ? 'yellow' : 'secondary' }}" class="mb-3 mr-2 text-sm">
                    <x-ts-icon icon="tabler.binary-tree" class="inline-block size-5" />
                    {{ __('person.descendants') }}
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/chart" color="{{ request()->routeIs('people.chart') ? 'yellow' : 'secondary' }}" class="mb-3 mr-2 text-sm">
                    <x-ts-icon icon="tabler.social" class="inline-block size-5" />
                    {{ __('app.family_chart') }}
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/history" color="{{ request()->routeIs('people.history') ? 'yellow' : 'cyan' }}" class="mb-3 mr-2 text-sm">
                    <x-ts-icon icon="tabler.history" class="inline-block size-5" />
                    {{ __('app.history') }}
                </x-ts-button>

                <x-ts-button href="/people/{{ $person->id }}/datasheet" color="{{ request()->routeIs('people.datasheet') ? 'yellow' : 'secondary' }}" class="mb-3 mr-2 text-sm">
                    <x-ts-icon icon="tabler.pdf" class="inline-block size-5" />
                    {{ __('app.datasheet') }}
                </x-ts-button>
            </div>
        </div>
    </div>
</div>
