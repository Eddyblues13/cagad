import { useState, useEffect } from 'react'
import FadeIn from './FadeIn.jsx'

// Board and management data sourced from the CAGAD Governance and Management
// Composition. A short professional bio can be added to any person via the
// `bio` field — when present, the card becomes clickable and opens a modal.
const board = [
  { name: 'Dr Matthew Ayibakuro', role: 'Board Chairman', bio: '', photo: '/Dr Matthew Ayibakuro.jpeg' },
  { name: 'Nkiruka Uzodi', role: 'Member', bio: '', photo: '/Nkiru Uzodi.jpeg' },
  { name: 'Prof Ernest T. Aniche', role: 'Member', bio: '', photo: '/Prof Ernest T. Aniche 1.jpeg' },
  { name: 'Mfoniso Obinna', role: 'Member', bio: '', photo: '/Mfoniso Obinna.jpeg' },
  { name: 'Benjamin Ekeyi', role: 'Member', bio: '', photo: '/Benjamin Ekeyi.jpeg' },
  { name: 'Zainab Abdulfattah', role: 'Member', bio: '', photo: '/Zainab Abdulfattah.jpeg' },
  { name: 'Daniel Atikami', role: 'Member', bio: '', photo: '/Daniel Atikami 1.jpeg' },
  { name: 'Eseimokumo Albert Tony', role: 'Member', bio: '', photo: '/Eseimokumo Albert Tony.jpeg' },
  { name: 'Godstime Ezekiel', role: 'Member / Board Secretary', bio: '', photo: '/Godstime Ezekiel.jpeg' },
]

const management = [
  { name: 'Godstime Ezekiel', role: 'Team Lead & Executive Director', bio: '', photo: '/Godstime Ezekiel.jpeg' },
  { name: 'Mfoniso Obinna', role: 'Head, Funding & Partnerships', bio: '', photo: '/Mfoniso Obinna.jpeg' },
  { name: 'Emmanuel Ifeanyi', role: 'Head, Finance & Administration', bio: '', photo: '/Emmanuel Ifeanyi 1.jpeg' },
  { name: 'Edwin Efemini', role: 'Head, Technology & Innovation', bio: '' },
  { name: 'Yeboah Acquah', role: 'Head, Communications & Public Relations', bio: '', photo: '/Yeboah Acquah.jpeg' },
  { name: 'John Awojobi', role: 'Head, Research & Policy', bio: '', photo: '/John Awojobi.jpeg' },
  { name: 'Ayodeji Ayinde', role: 'Head, Legal & Compliance', bio: '', photo: '/Ayodeji Ayinde.jpeg' },
  { name: 'Young Mfon', role: 'Program Lead, International Affairs (IAP)', bio: '', photo: '/Young Mfon.jpeg' },
  { name: 'Oyinmiebi Peter', role: 'Program Lead, Elections Credibility Monitoring (ECMP)', bio: '', photo: '/Oyinmiebi Peter.jpeg' },
]

const slugify = (name) =>
  name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')

const initials = (name) => {
  const parts = name.replace(/^(Dr|Prof|Mr|Mrs|Ms)\.?\s+/i, '').trim().split(/\s+/)
  const first = parts[0]?.[0] ?? ''
  const last = parts.length > 1 ? parts[parts.length - 1][0] : ''
  return (first + last).toUpperCase()
}

function PersonCard({ person, onOpen }) {
  // Drop a photo into /public/team/<slug>.jpg and it appears automatically;
  // until then we show a brand-styled initials avatar.
  const [imgOk, setImgOk] = useState(true)
  const hasBio = Boolean(person.bio)
  const photo = person.photo ? encodeURI(person.photo) : `/team/${slugify(person.name)}.jpg`

  const cardProps = hasBio
    ? {
        role: 'button',
        tabIndex: 0,
        onClick: () => onOpen(person),
        onKeyDown: (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault()
            onOpen(person)
          }
        },
        'aria-label': `Read bio of ${person.name}`,
      }
    : {}

  return (
    <article className={`person-card ${hasBio ? 'has-bio' : ''}`} {...cardProps}>
      <div className="person-photo">
        {imgOk ? (
          <img
            src={photo}
            alt={`Portrait of ${person.name}, ${person.role}`}
            loading="lazy"
            onError={() => setImgOk(false)}
          />
        ) : (
          <span className="person-avatar" aria-hidden="true">
            {initials(person.name)}
          </span>
        )}
      </div>
      <div className="person-body">
        <h4 className="person-name">{person.name}</h4>
        <p className="person-role">{person.role}</p>
        {hasBio && <span className="person-readbio">Read bio</span>}
      </div>
    </article>
  )
}

function BioModal({ person, onClose }) {
  useEffect(() => {
    const onKey = (e) => e.key === 'Escape' && onClose()
    document.addEventListener('keydown', onKey)
    document.body.style.overflow = 'hidden'
    return () => {
      document.removeEventListener('keydown', onKey)
      document.body.style.overflow = ''
    }
  }, [onClose])

  if (!person) return null
  const photo = person.photo ? encodeURI(person.photo) : `/team/${slugify(person.name)}.jpg`

  return (
    <div className="bio-modal-overlay" onClick={onClose} role="presentation">
      <div
        className="bio-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="bio-modal-name"
        onClick={(e) => e.stopPropagation()}
      >
        <button className="bio-modal-close" onClick={onClose} aria-label="Close bio">
          <i className="fas fa-times" aria-hidden="true"></i>
        </button>
        <div className="bio-modal-header">
          <div className="bio-modal-photo">
            <img src={photo} alt={`Portrait of ${person.name}`} onError={(e) => { e.currentTarget.style.display = 'none' }} />
          </div>
          <div>
            <h3 id="bio-modal-name">{person.name}</h3>
            <p className="person-role">{person.role}</p>
          </div>
        </div>
        <p className="bio-modal-text">{person.bio}</p>
      </div>
    </div>
  )
}

function PeopleGrid({ people, label, onOpen }) {
  return (
    <div className="row justify-content-center" role="list" aria-label={label}>
      {people.map((person) => (
        <div className="col-sm-6 col-lg-4 mb-4" key={`${person.name}-${person.role}`} role="listitem">
          <FadeIn>
            <PersonCard person={person} onOpen={onOpen} />
          </FadeIn>
        </div>
      ))}
    </div>
  )
}

export default function LeadershipSection() {
  const [active, setActive] = useState(null)

  return (
    <section id="leadership" className="leadership-section section-padding" aria-labelledby="leadership-heading">
      <div className="container">
        <FadeIn>
          <div className="text-center mb-2">
            <span className="team-eyebrow">Our Governance</span>
          </div>
          <h2 className="section-title center-title text-center" id="leadership-heading">Board of Directors</h2>
          <p className="team-intro text-center mx-auto">
            The diverse CAGAD Board of Directors, comprising experts in governance, democracy, public policy,
            law, and international development, providing strong governance oversight and strategic guidance.
          </p>
        </FadeIn>

        <PeopleGrid people={board} label="CAGAD Board of Directors" onOpen={setActive} />

        <FadeIn>
          <div className="text-center mb-2 mt-5">
            <span className="team-eyebrow">Our Team</span>
          </div>
          <h2 className="section-title center-title text-center" id="management-heading">Management Team</h2>
          <p className="team-intro text-center mx-auto">
            Meet the exceptional and passionate team working to strengthen democracy, good governance,
            accountability, security, and sustainable development in Nigeria and Africa.
          </p>
        </FadeIn>

        <PeopleGrid people={management} label="CAGAD Management Team" onOpen={setActive} />

        <FadeIn>
          <div className="staff-coming-soon text-center">
            <h3>Our Staff</h3>
            <p>Profiles of our wider team are coming soon.</p>
          </div>
        </FadeIn>
      </div>

      {active && <BioModal person={active} onClose={() => setActive(null)} />}
    </section>
  )
}
