<?php

namespace Webkul\Customer\Data;

class MeasurementFields
{
    public const GENDER_MALE = 'male';

    public const GENDER_FEMALE = 'female';

    public const GROUP_UPPER_BODY = 'upper_body';

    public const GROUP_LOWER_BODY = 'lower_body';

    public const GROUP_CUSTOM = 'custom';

    public const UNIT_CM = 'cm';

    public const UNIT_INCHES = 'inches';

    public const FIT_SLIM = 'slim_fit';

    public const FIT_REGULAR = 'regular_fit';

    public const FIT_RELAXED = 'relaxed_fit';

    public const FIT_FLOWING_LOOSE = 'flowing_loose_fit';

    public const FIT_NOTE_OTHER = 'other';

    /**
     * Canonical field definitions keyed by gender and body group.
     */
    public static function definitions(): array
    {
        return [
            self::GENDER_MALE => [
                self::GROUP_UPPER_BODY => [
                    self::field('shoulder', 'Shoulder', 'Measure across the back from shoulder tip to shoulder tip.'),
                    self::field('neck', 'Neck', 'Measure around the base of the neck where the collar sits.'),
                    self::field('chest', 'Chest', 'Measure around the fullest part of the chest, keeping the tape level.'),
                    self::field('stomach', 'Stomach', 'Measure around the fullest part of the stomach, usually at navel level.'),
                    self::field('waist', 'Waist', 'Measure around the natural waistline, above the hip bones.'),
                    self::field('round_sleeve', 'Round Sleeve', 'Measure around the fullest part of the upper arm.'),
                    self::field('sleeve_length', 'Sleeve Length', 'Measure from shoulder tip down the arm to the desired sleeve end.'),
                    self::field('top_length', 'Top Length', 'Measure from the highest shoulder point down to the desired top hem.'),
                ],
                self::GROUP_LOWER_BODY => [
                    self::field('thighs_laps', 'Thighs/Laps', 'Measure around the fullest part of the thigh.'),
                    self::field('round_knee', 'Round Knee', 'Measure around the knee at its widest point.'),
                    self::field('trouser_length', 'Trouser Length', 'Measure from the waist down the leg to the desired trouser hem.'),
                ],
            ],
            self::GENDER_FEMALE => [
                self::GROUP_UPPER_BODY => [
                    self::field('shoulder', 'Shoulder', 'Measure across the back from shoulder tip to shoulder tip.'),
                    self::field('bust', 'Bust', 'Measure around the fullest part of the bust, keeping the tape level.'),
                    self::field('waist', 'Waist', 'Measure around the natural waistline, above the hip bones.'),
                    self::field('round_sleeve', 'Round Sleeve', 'Measure around the fullest part of the upper arm.'),
                    self::field('sleeve_length', 'Sleeve Length', 'Measure from shoulder tip down the arm to the desired sleeve end.'),
                    self::field('top_length', 'Top Length', 'Measure from the highest shoulder point down to the desired top hem.'),
                ],
                self::GROUP_LOWER_BODY => [
                    self::field('hip', 'Hip', 'Measure around the fullest part of the hips and buttocks.'),
                    self::field('skirt_length', 'Skirt Length', 'Measure from the waist down to the desired skirt hem.'),
                    self::field('dress_length', 'Dress Length', 'Measure from the highest shoulder point down to the desired dress hem.'),
                    self::field('thighs_laps', 'Thighs/Laps', 'Measure around the fullest part of the thigh.'),
                    self::field('round_knee', 'Round Knee', 'Measure around the knee at its widest point.'),
                    self::field('shorts_length', 'Shorts Length', 'Measure from the waist down to the desired shorts hem.'),
                    self::field('trouser_length', 'Trouser Length', 'Measure from the waist down the leg to the desired trouser hem.'),
                ],
            ],
        ];
    }

    /**
     * Flat list of all canonical labels for admin product configuration.
     */
    public static function allLabels(): array
    {
        $labels = [];

        foreach (self::definitions() as $gender => $groups) {
            foreach ($groups as $group => $fields) {
                foreach ($fields as $field) {
                    $labels[$field['slug']] = $field['label'];
                }
            }
        }

        return array_values(array_unique($labels));
    }

    /**
     * Slug => label map (deduplicated by slug).
     */
    public static function labelMap(): array
    {
        $map = [];

        foreach (self::definitions() as $groups) {
            foreach ($groups as $fields) {
                foreach ($fields as $field) {
                    $map[$field['slug']] = $field['label'];
                }
            }
        }

        return $map;
    }

    /**
     * Group display labels.
     */
    public static function groupLabels(): array
    {
        return [
            self::GROUP_UPPER_BODY => 'Upper Body',
            self::GROUP_LOWER_BODY => 'Lower Body',
            self::GROUP_CUSTOM     => 'Custom',
        ];
    }

    /**
     * Fit preference options (value => label).
     */
    public static function fitPreferenceOptions(): array
    {
        return [
            self::FIT_SLIM          => 'Slim Fit',
            self::FIT_REGULAR       => 'Regular Fit',
            self::FIT_RELAXED       => 'Relaxed Fit',
            self::FIT_FLOWING_LOOSE => 'Flowing/Loose Fit',
        ];
    }

    /**
     * Fit note options (value => label). Stored as a JSON array on the profile.
     */
    public static function fitNoteOptions(): array
    {
        return [
            'broad_shoulders'              => 'Broad shoulders',
            'long_arms'                    => 'Long arms',
            'short_arms'                   => 'Short arms',
            'large_hips'                   => 'Large hips',
            'big_tummy'                    => 'Big tummy',
            'very_slim_frame'              => 'Very slim frame',
            'tall_frame'                   => 'Tall frame',
            'short_frame'                  => 'Short frame',
            'prefer_modest_loose_clothing' => 'Prefer modest/loose clothing',
            self::FIT_NOTE_OTHER           => 'Other',
        ];
    }

    /**
     * Resolve profile gender to form gender key.
     */
    public static function resolveGender(?string $profileGender): string
    {
        return match (strtolower((string) $profileGender)) {
            'male'   => self::GENDER_MALE,
            'female' => self::GENDER_FEMALE,
            default  => self::GENDER_FEMALE,
        };
    }

    /**
     * Normalize stored unit values.
     */
    public static function normalizeUnit(?string $unit): string
    {
        $unit = strtolower(trim((string) $unit));

        return in_array($unit, ['cm', 'centimeter', 'centimeters'], true)
            ? self::UNIT_CM
            : self::UNIT_INCHES;
    }

    /**
     * Get fields for a gender as a flat slug-indexed array.
     */
    public static function fieldsForGender(string $gender): array
    {
        $gender = in_array($gender, [self::GENDER_MALE, self::GENDER_FEMALE], true)
            ? $gender
            : self::GENDER_FEMALE;

        $fields = [];

        foreach (self::definitions()[$gender] as $group => $groupFields) {
            foreach ($groupFields as $field) {
                $fields[$field['slug']] = array_merge($field, [
                    'group'  => $group,
                    'gender' => $gender,
                ]);
            }
        }

        return $fields;
    }

    /**
     * Get slugs for a gender.
     */
    public static function slugsForGender(string $gender): array
    {
        return array_keys(self::fieldsForGender($gender));
    }

    /**
     * Legacy slug mapping from old measurement_type + name to new canonical slug/group/gender hints.
     */
    public static function legacySlugMap(): array
    {
        return [
            'top' => [
                'shoulder'                 => ['slug' => 'shoulder', 'group' => self::GROUP_UPPER_BODY],
                'neck'                     => ['slug' => 'neck', 'group' => self::GROUP_UPPER_BODY, 'gender' => self::GENDER_MALE],
                'chest'                    => ['slug' => 'chest', 'group' => self::GROUP_UPPER_BODY, 'gender' => self::GENDER_MALE],
                'waist'                    => ['slug' => 'waist', 'group' => self::GROUP_UPPER_BODY],
                'round_sleeve'             => ['slug' => 'round_sleeve', 'group' => self::GROUP_UPPER_BODY],
                'sleeve_length'            => ['slug' => 'sleeve_length', 'group' => self::GROUP_UPPER_BODY],
                'top_length'               => ['slug' => 'top_length', 'group' => self::GROUP_UPPER_BODY],
                'bust'                     => ['slug' => 'bust', 'group' => self::GROUP_UPPER_BODY, 'gender' => self::GENDER_FEMALE],
                'upper_bust'               => null,
                'bust_point'               => null,
                'round_under_bust'         => null,
                'off_shoulder'             => null,
                'shoulder_to_under_bust'   => null,
                'half_length'              => null,
                'back_half_length'         => null,
            ],
            'skirt' => [
                'hip'                              => ['slug' => 'hip', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
                'waist_to_knee'                    => ['slug' => 'skirt_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
                'waist_to_below_knee_mid_length'   => ['slug' => 'skirt_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
                'waist_to_ankle_maxi_length'       => ['slug' => 'skirt_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
            ],
            'dress' => [
                'full_length'   => ['slug' => 'dress_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
                'mid_length'    => ['slug' => 'dress_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
                'knee_length'   => ['slug' => 'shorts_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
                'short_length'  => ['slug' => 'shorts_length', 'group' => self::GROUP_LOWER_BODY, 'gender' => self::GENDER_FEMALE],
            ],
            'trouser' => [
                'waist'                 => ['slug' => 'waist', 'group' => self::GROUP_UPPER_BODY],
                'thighs_laps'           => ['slug' => 'thighs_laps', 'group' => self::GROUP_LOWER_BODY],
                'round_knee'            => ['slug' => 'round_knee', 'group' => self::GROUP_LOWER_BODY],
                'full_trouser_length'   => ['slug' => 'trouser_length', 'group' => self::GROUP_LOWER_BODY],
                'in_seam'               => null,
                'out_seam'              => null,
                'crotch'                => null,
                'palazzo_thigh'         => null,
            ],
        ];
    }

    protected static function field(string $slug, string $label, string $help): array
    {
        return [
            'slug'  => $slug,
            'label' => $label,
            'help'  => $help,
        ];
    }
}
