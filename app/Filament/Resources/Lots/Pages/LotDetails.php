<?php

namespace App\Filament\Resources\Lots\Pages;

use Filament\Forms;
use App\Models\Lots;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\Lots\LotsResource;

class LotDetails extends Page implements Forms\Contracts\HasForms, Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = LotsResource::class;
    protected string $view = 'filament.resources.lots.lots-resource.pages.lot-details';

    public Lots $record;
    public ?array $data = [];
    public bool $isOpen = false;
    public ?int $editingId = null;

    public function mount(Lots $record): void
    {
        $this->record = $record->load('materials');
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('material')
                ->label('Material')
                ->required(),
            
            TextInput::make('colour')
                ->label('Colour'),
            
            Select::make('unit')
                ->label('Unit')
                ->options([
                    'Yards' => 'Yards',
                    'Roll' => 'Roll',
                    'Packet' => 'Packet',
                    'Cone' => 'Cone',
                ])
                ->required(),
            
            TextInput::make('rate')
                ->label('Rate')
                ->numeric()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $quantity = $get('quantity') ?? 0;
                    $set('price', $state * $quantity);
                }),
            
            TextInput::make('quantity')
                ->label('Quantity')
                ->numeric()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $rate = $get('rate') ?? 0;
                    $set('price', $state * $rate);
                }),
            
            TextInput::make('price')
                ->label('Price')
                ->numeric()
                ->disabled()
                ->dehydrated(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->record->materials()->getQuery())
            ->columns([
                TextColumn::make('material')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('colour')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->sortable(),
                TextColumn::make('rate')
                    ->numeric()
                    ->money('PKR')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('PKR')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('add_material')
                    ->label('Add Material')
                    ->icon('heroicon-o-plus')
                    ->form($this->getFormSchema())
                    ->action(function (array $data) {
                        $this->record->materials()->create([
                            'material' => $data['material'],
                            'colour' => $data['colour'] ?? null,
                            'unit' => $data['unit'],
                            'rate' => $data['rate'],
                            'quantity' => $data['quantity'],
                            'price' => $data['price'] ?? ($data['rate'] * $data['quantity']),
                            'dated' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Material added successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->form($this->getFormSchema())
                    ->action(function (array $data, $record) {
                        $record->update([
                            'material' => $data['material'],
                            'colour' => $data['colour'] ?? null,
                            'unit' => $data['unit'],
                            'rate' => $data['rate'],
                            'quantity' => $data['quantity'],
                            'price' => $data['price'] ?? ($data['rate'] * $data['quantity']),
                        ]);
                        
                        Notification::make()
                            ->title('Material updated successfully')
                            ->success()
                            ->send();
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();
                        
                        Notification::make()
                            ->title('Material deleted successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    // This is the method that's being called
    public function openModal()
    {
        Notification::make()
            ->title('Debug: openModal called')
            ->success()
            ->send();
            
        $this->editingId = null;
        $this->form->fill([
            'material' => '',
            'colour' => '',
            'unit' => '',
            'rate' => '',
            'quantity' => '',
            'price' => '',
        ]);
        $this->isOpen = true;
        
        Notification::make()
            ->title('Debug: isOpen set to ' . $this->isOpen)
            ->success()
            ->send();
    }

    public function editMaterial($id)
    {
        $material = $this->record->materials()->find($id);
        
        if ($material) {
            $this->editingId = $material->id;
            $this->form->fill([
                'material' => $material->material,
                'colour' => $material->colour,
                'unit' => $material->unit,
                'rate' => $material->rate,
                'quantity' => $material->quantity,
                'price' => $material->price,
            ]);
            $this->isOpen = true;
        }
    }

    public function saveMaterial()
    {
        $data = $this->form->getState();
        
        if ($this->editingId) {
            $this->record->materials()->where('id', $this->editingId)->update([
                'material' => $data['material'],
                'colour' => $data['colour'] ?? null,
                'unit' => $data['unit'],
                'rate' => $data['rate'],
                'quantity' => $data['quantity'],
                'price' => $data['price'] ?? ($data['rate'] * $data['quantity']),
            ]);
            
            $message = 'Material updated successfully';
        } else {
            $this->record->materials()->create([
                'material' => $data['material'],
                'colour' => $data['colour'] ?? null,
                'unit' => $data['unit'],
                'rate' => $data['rate'],
                'quantity' => $data['quantity'],
                'price' => $data['price'] ?? ($data['rate'] * $data['quantity']),
                'dated' => now(),
            ]);
            
            $message = 'Material added successfully';
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();

        $this->closeModal();
        $this->record->refresh();
    }
    
    public function deleteMaterial($id)
    {
        $this->record->materials()->where('id', $id)->delete();
        
        Notification::make()
            ->title('Material deleted successfully')
            ->success()
            ->send();
            
        $this->record->refresh();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->form->fill([]);
        $this->editingId = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Lots')
                ->url(route('filament.admin.resources.lots.index'))
                ->color('gray'),
        ];
    }
}