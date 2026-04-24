import FadeIn from './FadeIn.jsx'

const focusAreas = [
  {
    icon: 'fas fa-landmark',
    title: 'Governance',
    desc: 'Promoting good governance, strengthening public leadership integrity, and fostering institutional credibility.',
  },
  {
    icon: 'fas fa-scale-balanced',
    title: 'Accountability',
    desc: "Promoting transparency and accountability in governance, monitoring public expenditure, evaluating policies' impact and tracking promises and service delivery.",
  },
  {
    icon: 'fas fa-shield-alt',
    title: 'Security',
    desc: "Monitoring security situations and events, providing evidence-based analysis and reports, addressing human security gaps, and identifying sustainable solutions to improve citizens' and communities' safety and national and regional security.",
  },
  {
    icon: 'fas fa-vote-yea',
    title: 'Democracy',
    desc: 'Deepening democratic consolidation, monitoring democratic stability, and fostering citizen participation in governance.',
  },
  {
    icon: 'fas fa-dove',
    title: 'Peacebuilding',
    desc: 'Fostering peacebuilding, conflict resolution and stability in fragile or transition contexts.',
  },
  {
    icon: 'fas fa-seedling',
    title: 'Sustainable Development',
    desc: 'Promoting the SDGs, monitoring progress, and advocating for inclusive and sustainable development that meets the needs of present and future generations.',
  },
]

export default function FocusAreasSection() {
  const left = focusAreas.slice(0, 3)
  const right = focusAreas.slice(3)

  const renderArea = (area) => (
    <article className="focus-area-item" key={area.title} aria-label={`Focus area: ${area.title}`}>
      <div className="focus-area-icon" aria-hidden="true">
        <i className={area.icon}></i>
      </div>
      <div className="focus-area-content">
        <h3 className="h4">{area.title}</h3>
        <p>{area.desc}</p>
      </div>
    </article>
  )

  return (
    <section id="focus" className="section-padding bg-light" aria-labelledby="focus-heading">
      <div className="container">
        <h2 className="section-title center-title text-center mb-5" id="focus-heading">Focus Areas</h2>
        <div className="row">
          <div className="col-lg-6">
            <FadeIn>
              {left.map(renderArea)}
            </FadeIn>
          </div>
          <div className="col-lg-6">
            <FadeIn>
              {right.map(renderArea)}
            </FadeIn>
          </div>
        </div>
      </div>
    </section>
  )
}
