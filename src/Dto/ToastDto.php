<?php

namespace App\Dto;

class ToastDto
{
    private string $text;
    private string $state;

    final public const STATE_SUCCESS = 'SUCCESS';
    final public const STATE_ERROR = 'ERROR';
    final public const STATE_WARNING = 'WARNING';

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }
}
