# Team & Board photos

Drop portrait photos here and they appear automatically on the Board & Team
section — no code change needed.

## Naming

Use the person's full name, lowercased, with spaces and punctuation replaced by
hyphens, ending in `.jpg`. This matches the `slugify()` used in
`src/components/LeadershipSection.jsx`.

Examples:

| Person                  | File name                       |
| ----------------------- | ------------------------------- |
| Dr Matthew Ayibakuro    | `dr-matthew-ayibakuro.jpg`      |
| Nkiruka Uzodi           | `nkiruka-uzodi.jpg`             |
| Prof Ernest T. Aniche   | `prof-ernest-t-aniche.jpg`      |
| Godstime Ezekiel        | `godstime-ezekiel.jpg`          |
| Eseimokumo Albert Tony  | `eseimokumo-albert-tony.jpg`    |

Any person without a matching photo shows a brand-coloured initials avatar, so
the section always looks complete.

- Recommended: portrait orientation, roughly 4:5 ratio (e.g. 800×1000px).
- To use a custom path/filename instead, set a `photo` field on that person in
  `LeadershipSection.jsx`.

## Bios

Each person has a `bio` field in `LeadershipSection.jsx` (empty for now). Once a
bio is filled in, that card becomes clickable and opens a bio modal
automatically.
