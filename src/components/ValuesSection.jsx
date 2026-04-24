import FadeIn from './FadeIn.jsx'

const values = [
  { icon: 'fas fa-star', title: 'Excellence', desc: 'We strive for the highest quality in all our research, analysis, and recommendations.' },
  { icon: 'fas fa-balance-scale', title: 'Nonpartisanship', desc: 'We maintain impartiality and do not align with any political party or ideology.' },
  { icon: 'fas fa-shield-alt', title: 'Integrity', desc: 'We uphold the highest ethical standards in all our activities and relationships.' },
  { icon: 'fas fa-users', title: 'Inclusivity', desc: 'We ensure diverse voices and perspectives are represented in our work.' },
  { icon: 'fas fa-binoculars', title: 'Transparency', desc: 'We are open about our methods, funding, and decision-making processes.' },
  { icon: 'fas fa-crosshairs', title: 'Objectivity', desc: 'We base our findings and recommendations on evidence and rigorous analysis.' },
]

export default function ValuesSection() {
  return (
    <section className="values-section section-padding" aria-labelledby="values-heading" id="values">
      <div className="container">
        <h2 className="section-title center-title text-center mb-5" id="values-heading">Our Values</h2>
        <div className="row" role="list" aria-label="CAGAD core values">
          {values.map((v) => (
            <div className="col-md-6 col-lg-4 mb-4" key={v.title} role="listitem">
              <FadeIn>
                <article className="value-box" aria-label={`Core value: ${v.title}`}>
                  <div className="value-icon" aria-hidden="true">
                    <i className={v.icon}></i>
                  </div>
                  <h4>{v.title}</h4>
                  <p>{v.desc}</p>
                </article>
              </FadeIn>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
