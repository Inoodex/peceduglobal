/**
 * Application workflow — single source of truth for the 7-step progress tracker.
 *
 * Order matters: the index of each step defines its position in the stepper.
 */

export const APPLICATION_STATUSES = [
  { value: 'document_submitted',    label: 'Document Submitted',    shortLabel: 'Docs',      color: 'blue'    },
  { value: 'application',           label: 'Application',           shortLabel: 'Applied',   color: 'indigo'  },
  { value: 'offer_letter',          label: 'Offer Letter',          shortLabel: 'Offer',     color: 'cyan'    },
  { value: 'deposit_received',      label: 'Deposit Received',      shortLabel: 'Deposit',   color: 'amber'   },
  { value: 'enrollment_confirmed',  label: 'Enrollment Confirmed',  shortLabel: 'Enrolled',  color: 'teal'    },
  { value: 'visa_processing',       label: 'Visa Processing',       shortLabel: 'Visa',      color: 'purple'  },
  { value: 'enrolled',              label: 'Enrolled',              shortLabel: 'Done',      color: 'green'   },
];

/** Terminal statuses that exist outside the main linear workflow. */
export const APPLICATION_TERMINAL_STATUSES = [
  { value: 'rejected', label: 'Rejected', color: 'red' },
];

export const ALL_APPLICATION_STATUSES = [
  ...APPLICATION_STATUSES,
  ...APPLICATION_TERMINAL_STATUSES,
];

/** Convenience list for <option> elements. */
export const STATUS_OPTIONS = ALL_APPLICATION_STATUSES.map((s) => ({
  value: s.value,
  label: s.label,
}));

/**
 * Returns the 0-based step index for a given status value.
 * Returns -1 for statuses that are not part of the linear workflow (e.g. rejected).
 */
export const getStepIndex = (status) =>
  APPLICATION_STATUSES.findIndex((s) => s.value === status);

/** Total number of steps in the linear workflow. */
export const TOTAL_STEPS = APPLICATION_STATUSES.length;

/** Completion percentage (0–100) for a given status, rounded. */
export const getProgressPercent = (status) => {
  const idx = getStepIndex(status);
  if (idx < 0) return 0;
  return Math.round(((idx + 1) / TOTAL_STEPS) * 100);
};

/** Finds the full status definition (label/color) for a given value. */
export const findStatus = (status) =>
  ALL_APPLICATION_STATUSES.find((s) => s.value === status);

/** Human readable label for a status value, falling back to a title-cased value. */
export const getStatusLabel = (status) => {
  const found = findStatus(status);
  if (found) return found.label;
  return status ? status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) : '—';
};

/**
 * Tailwind classes for badge/pill rendering per status color.
 * Keys mirror the `color` field in the status definitions above.
 */
export const STATUS_BADGE_CLASSES = {
  blue:    'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/10 dark:text-blue-500 dark:border-blue-900/20',
  indigo:  'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/10 dark:text-indigo-500 dark:border-indigo-900/20',
  cyan:    'bg-cyan-50 text-cyan-700 border-cyan-100 dark:bg-cyan-900/10 dark:text-cyan-500 dark:border-cyan-900/20',
  amber:   'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/10 dark:text-amber-500 dark:border-amber-900/20',
  teal:    'bg-teal-50 text-teal-700 border-teal-100 dark:bg-teal-900/10 dark:text-teal-500 dark:border-teal-900/20',
  purple:  'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/10 dark:text-purple-500 dark:border-purple-900/20',
  green:   'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/10 dark:text-green-500 dark:border-green-900/20',
  emerald: 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/10 dark:text-emerald-500 dark:border-emerald-900/20',
  red:     'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/10 dark:text-red-500 dark:border-red-900/20',
  gray:    'bg-gray-50 text-gray-700 border-gray-100 dark:bg-gray-900/10 dark:text-gray-400 dark:border-gray-800',
};

/** Returns the badge classes for a status value. */
export const getStatusBadgeClass = (status) => {
  const found = findStatus(status);
  return STATUS_BADGE_CLASSES[found?.color] || STATUS_BADGE_CLASSES.gray;
};
