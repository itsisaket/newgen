<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Step indicator for the auto-chaining registration flow (สมัครสมาชิก /
 * เพิ่มครัวเรือน -> เพิ่มสวน -> เพิ่มแปลง -> เครื่องมือวิจัย). Purely
 * visual - the chaining itself is done by the controllers' redirect()
 * targets, not by this component.
 */
class WizardSteps extends Component
{
    public function __construct(public int $current = 1)
    {
    }

    public function render(): View
    {
        return view('components.wizard-steps');
    }
}
