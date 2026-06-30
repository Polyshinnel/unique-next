<?php

namespace App\Filament\Pages;

use App\Domain\Contact\Models\Contact;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

class Contacts extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Контакты';

    protected static ?string $title = 'Контакты';

    protected static ?int $navigationSort = 95;

    protected static ?string $slug = 'contacts';

    protected Width|string|null $maxContentWidth = Width::Full;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public ?int $contactId = null;

    public function mount(): void
    {
        $this->contactId = $this->resolveContact()->getKey();

        $this->fillForm();
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? 'Контакты';
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->getContact())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->columns(2)
                ->schema([
                    TextInput::make('address')
                        ->label('Адрес')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('address2')
                        ->label('Адрес 2')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('phone')
                        ->label('Телефон')
                        ->type('tel')
                        ->maxLength(50),
                    TextInput::make('email')
                        ->label('Почта')
                        ->email()
                        ->maxLength(255),
                ]),
            Section::make('Расписание')
                ->columns(2)
                ->schema([
                    TextInput::make('work_schedule')
                        ->label('Расписание 1')
                        ->maxLength(255),
                    TextInput::make('work_schedule2')
                        ->label('Расписание 2')
                        ->maxLength(255),
                ]),
            Section::make('Карта')
                ->columns(2)
                ->schema([
                    TextInput::make('latitude')
                        ->label('Широта')
                        ->maxLength(255),
                    TextInput::make('longitude')
                        ->label('Долгота')
                        ->maxLength(255),
                ]),
            Section::make('Соцсети')
                ->columns(3)
                ->schema([
                    TextInput::make('telegram')
                        ->label('Telegram')
                        ->maxLength(255)
                        ->url(),
                    TextInput::make('vk')
                        ->label('Vk')
                        ->maxLength(255)
                        ->url(),
                    TextInput::make('max')
                        ->label('Max')
                        ->maxLength(255)
                        ->url(),
                ]),
            Section::make('Реквизиты')
                ->columns(2)
                ->schema([
                    TextInput::make('inn')
                        ->label('ИНН')
                        ->maxLength(20),
                    TextInput::make('ogrn')
                        ->label('ОГРН')
                        ->maxLength(20),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('contacts-form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make($this->getFormActions())
                        ->alignment($this->getFormActionsAlignment())
                        ->key('form-actions'),
                ]),
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::Start;
    }

    protected function fillForm(): void
    {
        $this->form->fill($this->getContact()->attributesToArray());
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            $this->getContact()->update($data);
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        Notification::make()
            ->title('Контакты сохранены')
            ->success()
            ->send();
    }

    protected function getContact(): Contact
    {
        if ($this->contactId !== null) {
            $contact = Contact::query()->find($this->contactId);

            if ($contact !== null) {
                return $contact;
            }
        }

        $contact = $this->resolveContact();
        $this->contactId = $contact->getKey();

        return $contact;
    }

    protected function resolveContact(): Contact
    {
        return Contact::query()->oldest('id')->first() ?? Contact::query()->create();
    }
}
