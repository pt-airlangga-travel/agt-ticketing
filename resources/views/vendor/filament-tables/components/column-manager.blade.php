@php
    use Filament\Support\Enums\GridDirection;
    use Filament\Tables\Enums\ColumnManagerResetActionPosition;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'applyAction',
    'columns' => null,
    'hasReorderableColumns',
    'hasToggleableColumns',
    'headingTag' => 'h3',
    'reorderAnimationDuration' => 300,
    'resetActionPosition' => ColumnManagerResetActionPosition::Header,
])

<div class="fi-ta-col-manager">
    <div
        x-data="{
            ...filamentTableColumnManager({
                columns: $wire.entangle('tableColumns'),
                isLive: {{ $applyAction->isVisible() ? 'false' : 'true' }},
            }),
            toggleAllColumns(forceState = null) {
                const cols = [];
                this.deferredColumns.forEach((item) => {
                    if (item.type === 'group' && item.columns) {
                        item.columns.forEach((c) => {
                            if (!c.isHidden && c.isToggleable !== false) {
                                cols.push(c);
                            }
                        });
                    } else if (!item.isHidden && item.isToggleable !== false) {
                        cols.push(item);
                    }
                });

                if (cols.length === 0) return;

                const anyOff = cols.some((c) => !c.isToggled);
                const newState = forceState !== null ? forceState : anyOff;

                cols.forEach((c) => {
                    c.isToggled = newState;
                });

                this.deferredColumns = [...this.deferredColumns];

                if (this.isLive) {
                    this.applyTableColumnManager();
                }
            },
            get allColumnsState() {
                const cols = [];
                this.deferredColumns.forEach((item) => {
                    if (item.type === 'group' && item.columns) {
                        item.columns.forEach((c) => {
                            if (!c.isHidden && c.isToggleable !== false) {
                                cols.push(c);
                            }
                        });
                    } else if (!item.isHidden && item.isToggleable !== false) {
                        cols.push(item);
                    }
                });

                if (cols.length === 0) return { checked: false, indeterminate: false };

                const onCount = cols.filter((c) => c.isToggled).length;
                if (onCount === cols.length) return { checked: true, indeterminate: false };
                if (onCount === 0) return { checked: false, indeterminate: false };
                return { checked: false, indeterminate: true };
            }
        }"
        class="fi-ta-col-manager-ctn"
    >
        <div class="fi-ta-col-manager-header">
            <{{ $headingTag }} class="fi-ta-col-manager-heading">
                {{ __('filament-tables::table.column_manager.heading') }}
            </{{ $headingTag }}>

            <div
                class="flex items-center text-xs"
                style="display: flex; align-items: center; gap: 12px; font-size: 0.8125rem;"
            >
                @if ($hasToggleableColumns)
                    <x-filament::link
                        color="gray"
                        tag="button"
                        x-on:click="toggleAllColumns(true)"
                    >
                        Tampilkan Semua
                    </x-filament::link>

                    <span class="text-gray-300 dark:text-gray-600" style="color: rgba(156, 163, 175, 0.6);">|</span>

                    <x-filament::link
                        color="gray"
                        tag="button"
                        x-on:click="toggleAllColumns(false)"
                    >
                        Sembunyikan Semua
                    </x-filament::link>
                @endif

                @if ($resetActionPosition === ColumnManagerResetActionPosition::Header)
                    @if ($hasToggleableColumns)
                        <span class="text-gray-300 dark:text-gray-600" style="color: rgba(156, 163, 175, 0.6);">|</span>
                    @endif
                    <div>
                        <x-filament::link
                            :attributes="
                                \Filament\Support\prepare_inherited_attributes(
                                    new ComponentAttributeBag([
                                        'color' => 'danger',
                                        'tag' => 'button',
                                        'wire:click' => 'resetTableColumnManager',
                                        'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                                        'wire:target' => 'resetTableColumnManager',
                                        'x-on:click' => 'resetDeferredColumns',
                                    ])
                                )
                            "
                        >
                            {{ __('filament-tables::table.column_manager.actions.reset.label') }}
                        </x-filament::link>
                    </div>
                @endif
            </div>
        </div>

        @if ($hasToggleableColumns)
            <div
                class="border-b border-gray-200 dark:border-white/10"
                style="margin-top: 16px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid rgba(156, 163, 175, 0.25);"
            >
                <div class="fi-ta-col-manager-item" style="padding-top: 0 !important;">
                    <label class="fi-ta-col-manager-label font-medium cursor-pointer" style="display: flex; align-items: center; gap: 10px;">
                        <input
                            type="checkbox"
                            class="fi-checkbox-input fi-valid"
                            id="column-manager-toggle-all"
                            x-bind:checked="allColumnsState.checked"
                            x-effect="$el.indeterminate = allColumnsState.indeterminate"
                            x-on:change="toggleAllColumns($el.checked)"
                        />
                        <span>Pilih Semua Kolom</span>
                    </label>
                </div>
            </div>
        @endif

        <div
            @if ($hasReorderableColumns)
                x-sortable
                x-on:end.stop="reorderColumns($event.target.sortable.toArray())"
                data-sortable-animation-duration="{{ $reorderAnimationDuration }}"
            @endif
            {{
                (new ComponentAttributeBag)
                    ->grid($columns, GridDirection::Column)
                    ->class(['fi-ta-col-manager-items'])
                    ->merge([
                        'style' => $hasToggleableColumns ? 'margin-top: 0px !important; padding-top: 8px;' : '',
                    ])
            }}
        >
            <template
                x-for="(column, index) in columns.filter((column) => ! column.isHidden && column.label)"
                x-bind:key="(column.type === 'group' ? 'group::' : 'column::') + column.name + '_' + index"
            >
                <div
                    @if ($hasReorderableColumns)
                        x-bind:x-sortable-item="column.type === 'group' ? 'group::' + column.name : 'column::' + column.name"
                    @endif
                >
                    <template x-if="column.type === 'group'">
                        <div class="fi-ta-col-manager-group">
                            <div class="fi-ta-col-manager-item">
                                <label class="fi-ta-col-manager-label">
                                    @if ($hasToggleableColumns)
                                        <input
                                            type="checkbox"
                                            class="fi-checkbox-input fi-valid"
                                            x-bind:id="'group-' + column.name"
                                            x-bind:checked="(groupedColumns[column.name] || {}).checked || false"
                                            x-bind:disabled="(groupedColumns[column.name] || {}).disabled || false"
                                            x-effect="$el.indeterminate = (groupedColumns[column.name] || {}).indeterminate || false"
                                            x-on:change="toggleGroup(column.name)"
                                        />
                                    @endif

                                    <span x-html="column.label"></span>
                                </label>

                                @if ($hasReorderableColumns)
                                    <button
                                        x-sortable-handle
                                        x-on:click.stop
                                        class="fi-ta-col-manager-reorder-handle fi-icon-btn"
                                        type="button"
                                    >
                                        {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::Bars2, alias: \Filament\Tables\View\TablesIconAlias::REORDER_HANDLE) }}
                                    </button>
                                @endif
                            </div>
                            <div
                                @if ($hasReorderableColumns)
                                    x-sortable
                                    x-on:end.stop="reorderGroupColumns($event.target.sortable.toArray(), column.name)"
                                    data-sortable-animation-duration="{{ $reorderAnimationDuration }}"
                                @endif
                                class="fi-ta-col-manager-group-items"
                            >
                                <template
                                    x-for="
                                        (groupColumn, index) in
                                            column.columns.filter((column) => ! column.isHidden && column.label)
                                    "
                                    x-bind:key="'column::' + groupColumn.name + '_' + index"
                                >
                                    <div
                                        @if ($hasReorderableColumns)
                                            x-bind:x-sortable-item="'column::' + groupColumn.name"
                                        @endif
                                    >
                                        <div class="fi-ta-col-manager-item">
                                            <label
                                                class="fi-ta-col-manager-label"
                                            >
                                                @if ($hasToggleableColumns)
                                                    <input
                                                        type="checkbox"
                                                        class="fi-checkbox-input fi-valid"
                                                        x-bind:id="'column-' + groupColumn.name.replace('.', '-')"
                                                        x-bind:checked="(getColumn(groupColumn.name, column.name) || {}).isToggled || false"
                                                        x-bind:disabled="(getColumn(groupColumn.name, column.name) || {}).isToggleable === false"
                                                        x-on:change="toggleColumn(groupColumn.name, column.name)"
                                                    />
                                                @endif

                                                <span
                                                    x-html="groupColumn.label"
                                                ></span>
                                            </label>

                                            @if ($hasReorderableColumns)
                                                <button
                                                    x-sortable-handle
                                                    x-on:click.stop
                                                    class="fi-ta-col-manager-reorder-handle fi-icon-btn"
                                                    type="button"
                                                >
                                                    {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::Bars2, alias: \Filament\Tables\View\TablesIconAlias::REORDER_HANDLE) }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="column.type !== 'group'">
                        <div class="fi-ta-col-manager-item">
                            <label class="fi-ta-col-manager-label">
                                @if ($hasToggleableColumns)
                                    <input
                                        type="checkbox"
                                        class="fi-checkbox-input fi-valid"
                                        x-bind:id="'column-' + column.name.replace('.', '-')"
                                        x-bind:checked="(getColumn(column.name, null) || {}).isToggled || false"
                                        x-bind:disabled="(getColumn(column.name, null) || {}).isToggleable === false"
                                        x-on:change="toggleColumn(column.name)"
                                    />
                                @endif

                                <span x-html="column.label"></span>
                            </label>

                            @if ($hasReorderableColumns)
                                <button
                                    x-sortable-handle
                                    x-on:click.stop
                                    class="fi-ta-col-manager-reorder-handle fi-icon-btn"
                                    type="button"
                                >
                                    {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::Bars2, alias: \Filament\Tables\View\TablesIconAlias::REORDER_HANDLE) }}
                                </button>
                            @endif
                        </div>
                    </template>
                </div>
            </template>
        </div>

        @if ($applyAction->isVisible() || $resetActionPosition === ColumnManagerResetActionPosition::Footer)
            <div class="fi-ta-col-manager-actions-ctn">
                @if ($applyAction->isVisible())
                    {{ $applyAction }}
                @endif

                @if ($resetActionPosition === ColumnManagerResetActionPosition::Footer)
                    <x-filament::button
                        color="danger"
                        wire:click="resetTableColumnManager"
                        x-on:click="resetDeferredColumns"
                    >
                        {{ __('filament-tables::table.column_manager.actions.reset.label') }}
                    </x-filament::button>
                @endif
            </div>
        @endif
    </div>
</div>
