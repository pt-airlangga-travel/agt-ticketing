# Custom Attach Action Pattern in Filament Relation Managers

When you need to add an action to a RelationManager that allows a user to select an existing record from the database to attach (and optionally create a new one), **DO NOT** use `AttachAction::make()->searchable()`.

Instead, use a standard `Action` with a `Select` form field and handle the sync manually:

1. Create a standard `Action` (`use Filament\Actions\Action;` or `use Filament\Tables\Actions\Action;` depending on availability).
2. Define a `form()` containing a `Select::make('record_id')`.
3. Chain `->options(...)`, `->searchable()`, and `->preload()` to the `Select`.
4. Use `->createOptionForm(...)` and `->createOptionUsing(...)` on the `Select` if creation is needed.
5. In the `->action(function (array $data, $livewire))` closure, manually attach the record:
   `$livewire->getOwnerRecord()->relationName()->syncWithoutDetaching([$data['record_id']]);`
