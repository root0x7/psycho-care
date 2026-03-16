<?php

namespace App\Enums;

enum DoctorExperienceType: string
{
    case FormalEducation    = 'formal_education';
    case AdvancedTraining   = 'advanced_training';
    case WorkHistory        = 'work_history';

    public function label(): string
    {
        return match ($this) {
            self::FormalEducation  => 'Formal Education',
            self::AdvancedTraining => 'Advanced Training / Courses',
            self::WorkHistory      => 'Work History',
        };
    }
}
