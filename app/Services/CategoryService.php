<?php
/**
 * CategoryService
 * Centralized, standardized academic category definitions and normalizer across all modules.
 * Ensures 100% naming consistency between Upload & Scan, Department Vault, Export, and Dashboard.
 */

class CategoryService {
    /**
     * Official 7 Standard Academic Categories (aligned with Upload & Scan and CHED/AACCUP criteria)
     */
    public const CATEGORIES = [
        'Research Publication' => [
            'key' => 'Research Publication',
            'label' => 'Research Publication',
            'short_label' => 'Research Publication',
            'icon' => 'fa-solid fa-book-bookmark',
            'badge_bg' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800'
        ],
        'Instructional Material' => [
            'key' => 'Instructional Material',
            'label' => 'Instructional Material / Course Module',
            'short_label' => 'Instructional Material',
            'icon' => 'fa-solid fa-graduation-cap',
            'badge_bg' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800'
        ],
        'Seminar' => [
            'key' => 'Seminar',
            'label' => 'Seminar / Webinar',
            'short_label' => 'Seminar / Webinar',
            'icon' => 'fa-solid fa-users',
            'badge_bg' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
        ],
        'Training' => [
            'key' => 'Training',
            'label' => 'Training / Bootcamp',
            'short_label' => 'Training / Bootcamp',
            'icon' => 'fa-solid fa-chalkboard-user',
            'badge_bg' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'
        ],
        'Workshop' => [
            'key' => 'Workshop',
            'label' => 'Workshop',
            'short_label' => 'Workshop',
            'icon' => 'fa-solid fa-laptop-code',
            'badge_bg' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800'
        ],
        'Award' => [
            'key' => 'Award',
            'label' => 'Award / Recognition',
            'short_label' => 'Award / Recognition',
            'icon' => 'fa-solid fa-trophy',
            'badge_bg' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'
        ],
        'Academic Milestone' => [
            'key' => 'Academic Milestone',
            'label' => 'Academic Milestone / Appointment',
            'short_label' => 'Academic Milestone',
            'icon' => 'fa-solid fa-award',
            'badge_bg' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800'
        ],
        'Other' => [
            'key' => 'Other',
            'label' => 'Other Academic Documents',
            'short_label' => 'Other',
            'icon' => 'fa-solid fa-file-circle-question',
            'badge_bg' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'
        ]
    ];

    /**
     * Map any colloquial or legacy category alias to its canonical database key
     */
    public static function normalize(string $category): string {
        $c = strtolower(trim($category));
        if (str_starts_with($c, 'other')) {
            return 'Other';
        }
        $aliasMap = [
            'research' => 'Research Publication',
            'research publication' => 'Research Publication',
            'research publications' => 'Research Publication',

            'instructional material' => 'Instructional Material',
            'instructional materials' => 'Instructional Material',
            'instructional material / course module' => 'Instructional Material',
            'modules & ims' => 'Instructional Material',
            'modules and ims' => 'Instructional Material',
            'course module' => 'Instructional Material',
            'module' => 'Instructional Material',

            'seminar' => 'Seminar',
            'seminars' => 'Seminar',
            'seminar / webinar' => 'Seminar',
            'seminars & webinars' => 'Seminar',
            'webinar' => 'Seminar',
            'webinars' => 'Seminar',

            'training' => 'Training',
            'trainings' => 'Training',
            'training / bootcamp' => 'Training',
            'trainings & bootcamps' => 'Training',
            'bootcamp' => 'Training',

            'workshop' => 'Workshop',
            'workshops' => 'Workshop',

            'award' => 'Award',
            'awards' => 'Award',
            'award / recognition' => 'Award',
            'awards & recognitions' => 'Award',
            'recognition' => 'Award',

            'academic milestone' => 'Academic Milestone',
            'academic milestones' => 'Academic Milestone',
            'milestone' => 'Academic Milestone',
            'milestones' => 'Academic Milestone',
            'appointment' => 'Academic Milestone',
            'academic milestone / appointment' => 'Academic Milestone'
        ];

        return $aliasMap[$c] ?? $category;
    }

    /**
     * Get list of all official category keys
     */
    public static function getKeys(): array {
        return array_keys(self::CATEGORIES);
    }

    /**
     * Get display label for a category
     */
    public static function getLabel(string $category): string {
        $norm = self::normalize($category);
        return self::CATEGORIES[$norm]['label'] ?? $category;
    }

    /**
     * Get short display label for a category
     */
    public static function getShortLabel(string $category): string {
        $norm = self::normalize($category);
        return self::CATEGORIES[$norm]['short_label'] ?? $category;
    }

    /**
     * Get icon class for category
     */
    public static function getIcon(string $category): string {
        $norm = self::normalize($category);
        return self::CATEGORIES[$norm]['icon'] ?? 'fa-solid fa-folder';
    }

    /**
     * Get badge styling class for category
     */
    public static function getBadgeClass(string $category): string {
        $norm = self::normalize($category);
        return self::CATEGORIES[$norm]['badge_bg'] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
    }
}
