<?php

namespace App\Filament\Resources\Collections\RelationManagers;

use App\Models\CollectionWash;
use App\Support\CatalogTerms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Les sous-collections par lavage d'un hub : /homme/jean-baggy/noir.
 *
 * Quatre lavages au plus, ceux que les gens cherchent nommément. En ouvrir
 * davantage diluerait le hub sans capter de requête supplémentaire.
 */
class WashPagesRelationManager extends RelationManager
{
    protected static string $relationship = 'washPages';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.collections.washes');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('wash')
                ->label(__('admin.collections.fields.wash'))
                ->options(fn (): array => $this->washOptions())
                ->required()
                ->native(false)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('collection_id', $this->getOwnerRecord()->getKey())),

            Textarea::make('intro')
                ->label(__('admin.collections.fields.wash_intro'))
                ->helperText(__('admin.collections.fields.wash_intro_hint'))
                ->rows(4)
                ->maxLength(800)
                ->columnSpanFull(),

            Repeater::make('faq')
                ->label(__('admin.collections.fields.faq'))
                ->schema([
                    TextInput::make('question')->label(__('admin.collections.fields.question'))->required()->maxLength(200),
                    Textarea::make('answer')->label(__('admin.collections.fields.answer'))->required()->rows(3)->maxLength(1200),
                ])
                ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                ->collapsible()
                ->collapsed()
                ->defaultItems(0)
                ->columnSpanFull(),

            TextInput::make('meta_title')->label(__('admin.common.meta_title'))->maxLength(190),
            TextInput::make('meta_description')->label(__('admin.common.meta_description'))->maxLength(320),

            TextInput::make('position')->label(__('admin.common.position'))->numeric()->default(0),
            Toggle::make('is_visible')->label(__('admin.common.visible'))->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('wash')
                    ->label(__('admin.collections.fields.wash'))
                    ->formatStateUsing(fn (string $state): string => app(CatalogTerms::class)->washLabel($state)),

                TextColumn::make('intro')
                    ->label(__('admin.collections.fields.wash_intro'))
                    ->limit(70)
                    ->color('gray'),

                IconColumn::make('is_visible')
                    ->label(__('admin.common.visible'))
                    ->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading(__('admin.collections.washes'));
    }

    /**
     * Seuls les quatre lavages indexables sont proposés.
     *
     * @return array<string, string>
     */
    private function washOptions(): array
    {
        $termes = app(CatalogTerms::class);
        $options = [];

        foreach (CollectionWash::INDEXABLE as $segment => $slug) {
            $wash = $termes->wash($slug);

            if ($wash !== null && $wash->is_active) {
                $options[$slug] = "{$wash->name} (/{$segment})";
            }
        }

        return $options;
    }
}
