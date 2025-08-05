<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    const TYPE_SUCCESS = 'SUCCESS';
    const TYPE_ERROR = 'ERROR';

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $type = null,
        public ?string $message = null,
    ) {
        if (!$type && session()->has(self::TYPE_SUCCESS))
        {
            $this->type = self::TYPE_SUCCESS;
            $this->message = session(self::TYPE_SUCCESS);
        } elseif (!$type && session()->has(self::TYPE_ERROR))
            {
            $this->type = self::TYPE_ERROR;
            $this->message = session(self::TYPE_ERROR);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.common.alert');
    }

    public function shouldRender(): bool
    {
        return session()->has(self::TYPE_SUCCESS) || session()->has(self::TYPE_ERROR);
    }
}
