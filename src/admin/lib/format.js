// Dates in the admin are shown in Lagos time, the backend's APP_TIMEZONE, so
// what editors see matches the publish times they type.
const TIME_ZONE = 'Africa/Lagos'

const formatters = {
  dateTime: { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' },
  date: { day: 'numeric', month: 'short', year: 'numeric' },
  longDate: { day: 'numeric', month: 'long', year: 'numeric' },
  monthYear: { month: 'short', year: 'numeric' },
  dayMonth: { day: 'numeric', month: 'short' },
  shortDayTime: { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' },
  fullDateTime: { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' },
  today: { weekday: 'long', day: 'numeric', month: 'long' },
  month: { month: 'short' },
  day: { day: 'numeric' },
}

/**
 * Format an ISO date with one of the styles above, e.g. format(iso, 'dateTime')
 * gives "11 Oct 2026, 09:00". Empty values give an empty string.
 */
export function format(iso, style) {
  if (!iso) return ''
  const formatted = new Intl.DateTimeFormat('en-GB', { ...formatters[style], timeZone: TIME_ZONE }).format(new Date(iso))
  // "Sunday, 11 October 2026 at 09:00" reads better without the first comma.
  return style === 'fullDateTime' ? formatted.replace(',', '') : formatted
}

const units = [
  ['year', 365 * 24 * 3600],
  ['month', 30 * 24 * 3600],
  ['week', 7 * 24 * 3600],
  ['day', 24 * 3600],
  ['hour', 3600],
  ['minute', 60],
]

/** "3 weeks ago", "in 2 days" or "just now". */
export function timeAgo(iso) {
  if (!iso) return ''

  const seconds = (new Date(iso).getTime() - Date.now()) / 1000
  if (Math.abs(seconds) < 45) return 'just now'

  const [unit, size] = units.find(([, length]) => Math.abs(seconds) >= length) ?? units[units.length - 1]
  return new Intl.RelativeTimeFormat('en', { numeric: 'always' }).format(Math.round(seconds / size), unit)
}

/**
 * The value for a datetime-local input. The API sends Lagos times with their
 * offset ("2026-10-11T09:00:00+01:00"), so the wall-clock part is the first 16
 * characters, and the backend reads the input's value back as Lagos time.
 */
export function toDateTimeInput(iso) {
  return iso ? iso.slice(0, 16) : ''
}

/** "Good morning", "Good afternoon" or "Good evening" for the current Lagos time. */
export function greeting() {
  const hour = Number(new Intl.DateTimeFormat('en-GB', { hour: 'numeric', hourCycle: 'h23', timeZone: TIME_ZONE }).format(new Date()))
  if (hour < 12) return 'Good morning'
  return hour < 17 ? 'Good afternoon' : 'Good evening'
}

/** "Wednesday 4 November", for the dashboard. */
export function today() {
  return format(new Date().toISOString(), 'today')
}

export function plural(count, word) {
  return `${count} ${count === 1 ? word : `${word}s`}`
}

/** Turn a title into the slug the backend will generate, for placeholders. */
export function slugify(value) {
  return value
    .normalize('NFKD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}
