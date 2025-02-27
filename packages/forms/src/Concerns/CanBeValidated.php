<?php

namespace Filament\Forms\Concerns;

use Filament\Forms\Components;
use Filament\Forms\Components\Component;
use Illuminate\Validation\ValidationException;

trait CanBeValidated
{
    /**
     * @return array<string, string>
     */
    public function getValidationAttributes(): array
    {
        $attributes = [];

        foreach ($this->getComponents(withHidden: true) as $component) {
            if ($component->isHiddenAndNotDehydrated()) {
                continue;
            }

            if ($component instanceof Components\Contracts\HasValidationRules) {
                $component->dehydrateValidationAttributes($attributes);
            }

            foreach ($component->getChildComponentContainers() as $container) {
                if ($container->isHidden()) {
                    continue;
                }

                $attributes = [
                    ...$attributes,
                    ...$container->getValidationAttributes(),
                ];
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    public function getValidationMessages(): array
    {
        $messages = [];

        foreach ($this->getComponents(withHidden: true) as $component) {
            if ($component->isHiddenAndNotDehydrated()) {
                continue;
            }

            if ($component instanceof Components\Contracts\HasValidationRules) {
                $component->dehydrateValidationMessages($messages);
            }

            foreach ($component->getChildComponentContainers() as $container) {
                if ($container->isHidden()) {
                    continue;
                }

                $messages = [
                    ...$messages,
                    ...$container->getValidationMessages(),
                ];
            }
        }

        return $messages;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function getValidationRules(): array
    {
        $rules = [];

        foreach ($this->getComponents(withHidden: true) as $component) {
            if ($component->isHiddenAndNotDehydrated()) {
                continue;
            }

            if ($component instanceof Components\Contracts\HasValidationRules) {
                $component->dehydrateValidationRules($rules);
            }

            foreach ($component->getChildComponentContainers() as $container) {
                if ($container->isHidden()) {
                    continue;
                }

                $rules = [
                    ...$rules,
                    ...$container->getValidationRules(),
                ];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function validate(): array
    {
        if (! count(array_filter(
            $this->getComponents(withHidden: true),
            fn (Component $component): bool => ! $component->isHiddenAndNotDehydrated(),
        ))) {
            return [];
        }

        $rules = $this->getValidationRules();

        if (! count($rules)) {
            return [];
        }

        return $this->getLivewire()->validate($rules, $this->getValidationMessages(), $this->getValidationAttributes());
    }

    /**
     * @param string $field
     * @param array<string, array>|null $rules
     * @param array<string, array<string, string>> $messages
     * @param array<string, string> $attributes
     * @param array<string, string> $dataOverrides
     * @return array<string, mixed>
     */
    public function validateOnly(string $field, ?array $rules = null, array $messages = [], array $attributes = [], array $dataOverrides = []): array
    {
        $rules ??= [];

        $component = $this->getComponent($field);
        if ($component instanceof Components\Contracts\HasValidationRules) {
            $component->dehydrateValidationRules($rules);
            $component->dehydrateValidationMessages($messages);
            $component->dehydrateValidationAttributes($attributes);
        }

        return $this->getLivewire()->validateOnly($field, $rules, $messages, $attributes, $dataOverrides);
    }
}
